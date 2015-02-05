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
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class YubikeyController extends Controller
{
    /**
     * @Template
     * @param Request $request
     * @param $procedureId
     * @return array|Response
     */
    public function verifyAction(Request $request, $procedureId)
    {
        $command = new VerifyYubikeyPublicIdCommand();
        $form = $this->createForm('ra_verify_yubikey_public_id', $command)->handleRequest($request);

        if ($form->isValid()) {
            $result = $this->getVettingService()->verifyYubikeyPublicId($procedureId, $command);

            if ($result->didPublicIdMatch()) {
                return $this->redirectToRoute('ra_vetting_verify_identity', ['procedureId' => $procedureId]);
            } elseif ($result->didOtpVerificationFail()) {
                $form->get('otp')->addError(new FormError('ra.verify_yubikey_command.otp.verification_error'));
            } else {
                $form->addError(new FormError('ra.prove_yubikey_possession.different_yubikey_used'));
            }
        }

        // OTP field is rendered empty in the template.
        return ['form' => $form->createView()];
    }

    /**
     * @return VettingService
     */
    private function getVettingService()
    {
        return $this->get('ra.service.vetting');
    }
}
