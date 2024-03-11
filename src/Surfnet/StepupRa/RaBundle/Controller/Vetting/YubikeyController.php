<?php

declare(strict_types = 1);

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

namespace Surfnet\StepupRa\RaBundle\Controller\Vetting;

use Psr\Log\LoggerInterface;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyPublicIdCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\VerifyYubikeyPublicIdType;
use Surfnet\StepupRa\RaBundle\Logger\ProcedureAwareLogger;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorAssertionService;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class YubikeyController extends AbstractController
{
    public function __construct(
        private readonly VettingService $vettingService,
        private readonly SecondFactorAssertionService $secondFactorAssertionService,
        private readonly ProcedureAwareLogger $procedureAwareLogger,
    ) {
    }

    #[Route(
        path: '/vetting-procedure/{procedureId}/verify-yubikey',
        name: 'ra_vetting_yubikey_verify',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('ROLE_RA')]
    public function __invoke(Request $request, string $procedureId): Response
    {
        $this->secondFactorAssertionService->assertSecondFactorEnabled('yubikey');

        $procedureLogger = $this->procedureAwareLogger->forProcedure($procedureId);
        $procedureLogger->notice('Requested Yubikey Verfication');

        if (!$this->vettingService->hasProcedure($procedureId)) {
            $procedureLogger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        $command = new VerifyYubikeyPublicIdCommand();
        $form = $this->createForm(VerifyYubikeyPublicIdType::class, $command)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->vettingService->verifyYubikeyPublicId($procedureId, $command);

            if ($result->didPublicIdMatch()) {
                $procedureLogger->notice('Yubikey Verified, redirecting to verify identity');

                return $this->redirectToRoute('ra_vetting_verify_identity', ['procedureId' => $procedureId]);
            }

            if ($result->wasOtpInvalid()) {
                $this->addFlash('error', 'ra.verify_yubikey_command.otp.otp_invalid');
            } elseif ($result->didOtpVerificationFail()) {
                $this->addFlash('error', 'ra.verify_yubikey_command.otp.verification_error');
            } else {
                $this->addFlash('error', 'ra.prove_yubikey_possession.different_yubikey_used');
            }

            $procedureLogger->notice('Yubikey could not be verified, added error to form');
        }

        $procedureLogger->notice('Rendering Yubikey Verification Form');
        // OTP field is rendered empty in the template.

        return $this->render(
            view: 'vetting/yubikey/verify.html.twig',
            parameters: ['form' => $form->createView()],
        );
    }
}
