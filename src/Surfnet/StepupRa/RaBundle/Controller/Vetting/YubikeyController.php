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

namespace Surfnet\StepupRa\RaBundle\Controller\Vetting;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyPublicIdCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\VerifyYubikeyPublicIdType;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class YubikeyController extends SecondFactorController
{
    /**
     * @Template
     * @param Request $request
     * @param string  $procedureId
     * @return array|Response
     */
    public function verifyAction(Request $request, string $procedureId)
    {
        $this->assertSecondFactorEnabled('yubikey');

        $this->denyAccessUnlessGranted(['ROLE_RA']);

        $logger = $this->get('ra.procedure_logger')->forProcedure($procedureId);
        $logger->notice('Requested Yubikey Verfication');

        if (!$this->getVettingService()->hasProcedure($procedureId)) {
            $logger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        $command = new VerifyYubikeyPublicIdCommand();
        $form = $this->createForm(VerifyYubikeyPublicIdType::class, $command)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->getVettingService()->verifyYubikeyPublicId($procedureId, $command);

            if ($result->didPublicIdMatch()) {
                $logger->notice('Yubikey Verified, redirecting to verify identity');

                return $this->redirectToRoute('ra_vetting_verify_identity', ['procedureId' => $procedureId]);
            }

            if ($result->wasOtpInvalid()) {
                $this->addFlash('error', 'ra.verify_yubikey_command.otp.otp_invalid');
            } elseif ($result->didOtpVerificationFail()) {
                $this->addFlash('error', 'ra.verify_yubikey_command.otp.verification_error');
            } else {
                $this->addFlash('error', 'ra.prove_yubikey_possession.different_yubikey_used');
            }

            $logger->notice('Yubikey could not be verified, added error to form');
        }

        $logger->notice('Rendering Yubikey Verification Form');
        // OTP field is rendered empty in the template.
        return ['form' => $form->createView()];
    }

    /**
     * @return VettingService
     */
    private function getVettingService(): VettingService
    {
        return $this->get('ra.service.vetting');
    }
}
