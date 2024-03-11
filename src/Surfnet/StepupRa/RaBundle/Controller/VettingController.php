<?php

/**
 * Copyright 2014 SURFnet bv
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\StepupRa\RaBundle\Controller;

use Psr\Log\LoggerInterface;
use Surfnet\StepupBundle\Service\SecondFactorTypeService;
use Surfnet\StepupBundle\Value\SecondFactorType;
use Surfnet\StepupRa\RaBundle\Command\StartVettingProcedureCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyIdentityCommand;
use Surfnet\StepupRa\RaBundle\Exception\DomainException;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Surfnet\StepupRa\RaBundle\Form\Type\StartVettingProcedureType;
use Surfnet\StepupRa\RaBundle\Form\Type\VerifyIdentityType;
use Surfnet\StepupRa\RaBundle\Logger\ProcedureAwareLogger;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorService;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\VerifiedSecondFactor;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class VettingController extends AbstractController
{
    public function __construct(
        private readonly VettingService $vettingService,
        private readonly SecondFactorService $secondFactorService,
        private readonly SecondFactorTypeService $secondFactorTypeService,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
        private readonly ProcedureAwareLogger $procedureAwareLogger,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) https://www.pivotaltracker.com/story/show/135045063
     * @SuppressWarnings(PHPMD.NPathComplexity)      https://www.pivotaltracker.com/story/show/135045063
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)      https://www.pivotaltracker.com/story/show/135045063
     */
    #[Route(
        path: '/',
        name: 'ra_vetting_search',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('ROLE_RA')]
    public function startProcedure(Request $request): Response
    {
        $this->logger->notice('Vetting Procedure Search started');

        $command = new StartVettingProcedureCommand();

        $form = $this->createForm(StartVettingProcedureType::class, $command)->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->logger->notice('No search submitted, displaying search by registration code form');

            return $this->render('vetting/start_procedure.html.twig', ['form' => $form->createView()]);
        }

        $secondFactor = $this->secondFactorService
            ->findVerifiedSecondFactorByRegistrationCode($command->registrationCode, $this->getUser()->getIdentity()->id);

        if (!$secondFactor instanceof VerifiedSecondFactor) {
            $this->addFlash('error', 'ra.form.start_vetting_procedure.unknown_registration_code');
            $this->logger->notice('Cannot start new vetting procedure, no second factor found');

            return $this->render('vetting/start_procedure.html.twig', ['form' => $form->createView()]);
        }

        $enabledSecondFactors = $this->getParameter('surfnet_stepup_ra.enabled_second_factors');
        if (!in_array($secondFactor->type, $enabledSecondFactors, true)) {
            $this->logger->warning(
                sprintf(
                    'An RA attempted vetting of disabled second factor "%s" of type "%s"',
                    $secondFactor->id,
                    $secondFactor->type,
                ),
            );

            return $this
                ->render(
                    'vetting/second_factor_type_disabled.html.twig',
                    ['secondFactorType' => $secondFactor->type],
                )
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $command->authorityId =  $this->getUser()->getIdentity()->id;
        $command->authorityLoa = $this->getUser()->getLoa();
        $command->secondFactor = $secondFactor;

        if ($this->vettingService->isExpiredRegistrationCode($command)) {
            $this->addFlash(
                'error',
                $this->translator
                    ->trans(
                        'ra.verify_identity.registration_code_expired',
                        [
                            '%self_service_url%' => $this->getParameter('surfnet_stepup_ra.self_service_url'),
                        ],
                    ),
            );

            $this->logger->notice(
                'Second factor registration code is expired',
                ['registration_requested_at' => $secondFactor->registrationRequestedAt->format('Y-m-d')],
            );

            return $this->render('vetting/start_procedure.html.twig', ['form' => $form->createView()]);
        }

        if (!$this->vettingService->isLoaSufficientToStartProcedure($command)) {
            $this->addFlash('error', 'ra.form.start_vetting_procedure.loa_insufficient');

            $this->logger->notice('Cannot start new vetting procedure, Authority LoA is insufficient');

            return $this->render('vetting/start_procedure.html.twig', ['form' => $form->createView()]);
        }

        $procedureId = $this->vettingService->startProcedure($command);

        $this->procedureAwareLogger
            ->forProcedure($procedureId)
            ->notice(sprintf('Starting new Vetting Procedure for second factor of type "%s"', $secondFactor->type));

        if ($this->vettingService->isProvePossessionSkippable($procedureId)) {
            $this->procedureAwareLogger
                ->forProcedure($procedureId)
                ->notice(sprintf('Vetting Procedure for second factor of type "%s" skips the possession proven step', $secondFactor->type));

            return $this->redirectToRoute('ra_vetting_verify_identity', ['procedureId' => $procedureId]);
        }

        $secondFactorType = new SecondFactorType($secondFactor->type);
        if ($secondFactorType->isYubikey()) {
            return $this->redirectToRoute('ra_vetting_yubikey_verify', ['procedureId' => $procedureId]);
        } elseif ($secondFactorType->isSms()) {
            return $this->redirectToRoute('ra_vetting_sms_send_challenge', ['procedureId' => $procedureId]);
        } elseif ($this->secondFactorTypeService->isGssf($secondFactorType)) {
            return $this->redirectToRoute(
                'ra_vetting_gssf_initiate',
                [
                    'procedureId' => $procedureId,
                    'provider'    => $secondFactor->type
                ],
            );
        } else {
            throw new RuntimeException(
                sprintf('RA does not support vetting procedure for second factor type "%s"', $secondFactor->type),
            );
        }
    }

    #[Route(
        path: '/vetting-procedure/{procedureId}/cancel',
        name: 'ra_vetting_cancel',
        methods: ['POST'],
    )]
    public function cancelProcedure($procedureId): RedirectResponse
    {
        $logger = $this->procedureAwareLogger->forProcedure($procedureId);

        if (!$this->vettingService->hasProcedure($procedureId)) {
            $logger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        $this->vettingService->cancelProcedure($procedureId);
        $this->addFlash('info', $this->translator->trans('ra.vetting.flash.cancelled'));

        return $this->redirectToRoute('ra_vetting_search');
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[Route(
        path: '/vetting-procedure/{procedureId}/verify-identity',
        name: 'ra_vetting_verify_identity',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('ROLE_RA')]
    public function verifyIdentity(Request $request, string $procedureId): Response
    {
        $logger = $this->procedureAwareLogger->forProcedure($procedureId);
        $logger->notice('Verify Identity Form requested');

        if (!$this->vettingService->hasProcedure($procedureId)) {
            $logger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        $command = new VerifyIdentityCommand();
        $form = $this->createForm(VerifyIdentityType::class, $command)->handleRequest($request);

        /** @var SubmitButton $cancelButton */
        $cancelButton = $form->get('cancel');
        if ($cancelButton->isClicked()) {
            $this->vettingService->cancelProcedure($procedureId);
            $this->addFlash('info', $this->translator->trans('ra.vetting.flash.cancelled'));

            return $this->redirectToRoute('ra_vetting_search');
        }

        $vettingService = $this->vettingService;
        $commonName = $vettingService->getIdentityCommonName($procedureId);

        $showForm = function ($error = null) use ($form, $commonName) {
            if ($error) {
                $this->addFlash('error', $error);
            }

            return $this->render('vetting/verify_identity.html.twig', ['commonName' => $commonName, 'form' => $form->createView()]);
        };

        if (!$form->isSubmitted() || !$form->isValid()) {
            $logger->notice('Verify Identity Form not submitted, displaying form');

            return $showForm();
        }

        try {
            $vettingService->verifyIdentity($procedureId, $command);
        } catch (DomainException $e) {
            $this->logger->error(
                "RA attempted to verify identity, but the vetting procedure does not allow it",
                ['exception' => $e, 'procedure' => $procedureId],
            );

            return $showForm('ra.verify_identity.identity_verification_failed');
        }

        try {
            $vetting = $vettingService->vet($procedureId);
            if ($vetting->isSuccessful()) {
                $logger->notice('Identity Verified, vetting completed');

                return $this->redirectToRoute('ra_vetting_completed', ['procedureId' => $procedureId]);
            }

            $logger->error('RA attempted to vet second factor, but the command failed');

            if (in_array(VettingService::REGISTRATION_CODE_EXPIRED_ERROR, $vetting->getErrors())) {
                $registrationCodeExpiredError = $this->translator
                    ->trans(
                        'ra.verify_identity.registration_code_expired',
                        [
                            '%self_service_url%' => $this->getParameter('surfnet_stepup_ra.self_service_url'),
                        ],
                    );

                return $showForm($registrationCodeExpiredError);
            }

            return $showForm('ra.verify_identity.second_factor_vetting_failed');
        } catch (DomainException $e) {
            $logger->error(
                "RA attempted to vet second factor, but the vetting procedure didn't allow it",
                ['exception' => $e],
            );

            return $showForm('ra.verify_identity.second_factor_vetting_failed');
        }
    }

    #[Route(
        path: '/vetting-procedure/{procedureId}/completed',
        name: 'ra_vetting_completed',
        methods: ['GET'],
    )]
    public function vettingCompleted(): Response
    {
        return $this->render('vetting/vetting_completed.html.twig');
    }
}
