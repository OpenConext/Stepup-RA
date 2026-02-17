<?php

/**
 * Copyright 2015 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Controller\Vetting;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Surfnet\StepupBundle\Command\SendSmsChallengeCommand;
use Surfnet\StepupBundle\Command\VerifyPossessionOfPhoneCommand;
use Surfnet\StepupBundle\Value\PhoneNumber\InternationalPhoneNumber;
use Surfnet\StepupRa\RaBundle\Form\Type\SendSmsChallengeType;
use Surfnet\StepupRa\RaBundle\Form\Type\VerifyPhoneNumberType;
use Surfnet\StepupRa\RaBundle\Logger\ProcedureAwareLogger;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorAssertionService;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class SmsController extends AbstractController
{
    public function __construct(
        private readonly VettingService $vettingService,
        private readonly TranslatorInterface $translator,
        private readonly SecondFactorAssertionService $secondFactorAssertionService,
        private readonly ProcedureAwareLogger $procedureAwareLogger,
    ) {
    }

    #[Route(
        path: '/vetting-procedure/{procedureId}/send-sms-challenge',
        name: 'ra_vetting_sms_send_challenge',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('ROLE_RA')]
    public function sendChallenge(Request $request, string $procedureId): Response
    {
        $this->secondFactorAssertionService->assertSecondFactorEnabled('sms');

        $logger = $this->procedureAwareLogger->forProcedure($procedureId);
        $logger->notice('Received request for Send SMS Challenge page');

        if (!$this->vettingService->hasProcedure($procedureId)) {
            $logger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        $command = new SendSmsChallengeCommand();
        $form = $this->createForm(SendSmsChallengeType::class, $command)->handleRequest($request);

        // Identifier = phone number as it is stored in the verified second factor event
        $secondFactorIdentifier = $this->vettingService->getSecondFactorIdentifier($procedureId);
        // Id = the UUID identifier of the second factor token
        $secondFactorId = $this->vettingService->getSecondFactorId($procedureId);

        $phoneNumber = InternationalPhoneNumber::fromStringFormat($secondFactorIdentifier);

        $otpRequestsRemaining = $this->vettingService->getSmsOtpRequestsRemainingCount($secondFactorId);
        $maximumOtpRequests = $this->vettingService->getSmsMaximumOtpRequestsCount();
        $viewVariables = ['otpRequestsRemaining' => $otpRequestsRemaining, 'maximumOtpRequests' => $maximumOtpRequests];

        if (!$form->isSubmitted() || !$form->isValid()) {
            $logger->notice('Form has not been submitted, not sending SMS, rendering Send SMS Challenge page');

            return $this->render(
                'vetting/sms/send_challenge.html.twig',
                array_merge(
                    $viewVariables,
                    ['phoneNumber' => $phoneNumber, 'form' => $form->createView()],
                ),
            );
        }

        $logger->notice('Sending of SMS Challenge has been requested, sending OTP via SMS');
        if ($this->vettingService->sendSmsChallenge($procedureId, $command)) {
            $logger->notice(
                'SMS Challenge successfully sent, redirecting to Proof of Possession page to verify challenge',
            );

            return $this->redirectToRoute('ra_vetting_sms_prove_possession', ['procedureId' => $procedureId]);
        }

        $this->addFlash('error', 'ra.sms_send_challenge.send_sms_challenge_failed');

        $logger->notice(
            'SMS Challenge could not be sent, added error to page to notify user and re-rendering send challenge page',
        );

        return $this->render(
            'vetting/sms/send_challenge.html.twig',
            array_merge(
                $viewVariables,
                ['phoneNumber' => $phoneNumber, 'form' => $form->createView()],
            ),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route(
        path: '/vetting-procedure/{procedureId}/verify-sms-challenge',
        name: 'ra_vetting_sms_prove_possession',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('ROLE_RA')]
    public function provePossession(Request $request, string $procedureId): Response
    {
        $this->secondFactorAssertionService->assertSecondFactorEnabled('sms');
        $logger = $this->procedureAwareLogger->forProcedure($procedureId);

        $logger->notice('Received request for Proof of Possession of SMS Second Factor page');
        $vettingService = $this->vettingService;
        $command = new VerifyPossessionOfPhoneCommand();
        $command->secondFactorId = $vettingService->getSecondFactorId($procedureId);
        $form = $this
            ->createForm(VerifyPhoneNumberType::class, $command, ['procedureId' => $procedureId])
            ->handleRequest($request);

        /** @var SubmitButton $cancelButton */
        $cancelButton = $form->get('cancel');
        if ($cancelButton->isClicked()) {
            $vettingService->cancelProcedure($procedureId);

            $this->addFlash('info', $this->translator->trans('ra.vetting.flash.cancelled'));

            return $this->redirectToRoute('ra_vetting_search');
        }

        if (!$form->isSubmitted() || !$form->isValid()) {
            $logger->notice(
                'SMS OTP was not submitted through form, rendering Proof of Possession of SMS Second Factor page',
            );

            return $this->render(
                'vetting/sms/prove_possession.html.twig',
                ['form' => $form],
            );
        }

        $logger->notice('SMS OTP has been entered, attempting to verify Proof of Possession');
        $verification = $vettingService->verifyPhoneNumber($procedureId, $command);
        if ($verification->wasSuccessful()) {
            $logger->notice('SMS OTP was valid, Proof of Possession given, redirecting to Identity Vetting page');

            return $this->redirectToRoute(
                'ra_vetting_verify_identity',
                ['procedureId' => $procedureId],
            );
        } elseif ($verification->didOtpExpire()) {
            $this->addFlash('error', 'ra.prove_phone_possession.challenge_expired');
        } elseif ($verification->wasAttemptedTooManyTimes()) {
            $this->addFlash('error', 'ra.prove_phone_possession.too_many_attempts');
        } else {
            $this->addFlash('error', 'ra.prove_phone_possession.challenge_response_incorrect');
        }

        $logger->notice('SMS OTP verification failed - Proof of Possession denied, added error to form');

        return $this->render(
            'vetting/sms/prove_possession.html.twig',
            ['form' => $form],
        );
    }
}
