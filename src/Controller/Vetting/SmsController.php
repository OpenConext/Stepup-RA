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
use Surfnet\StepupRa\RaBundle\Command\SendSmsChallengeCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyPhoneNumberCommand;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SmsController extends Controller
{
    /**
     * @Template
     * @param Request $request
     * @param string $procedureId
     * @return array|Response
     */
    public function sendChallengeAction(Request $request, $procedureId)
    {
        $command = new SendSmsChallengeCommand();
        $form = $this->createForm('ra_send_sms_challenge', $command)->handleRequest($request);

        $vettingService = $this->getVettingService();
        $phoneNumber = $vettingService->getSecondFactorIdentifier($procedureId);

        if (!$form->isValid()) {
            return [
                'phoneNumber' => $phoneNumber,
                'form'        => $form->createView()
            ];
        }

        if ($vettingService->sendSmsChallenge($procedureId, $command)) {
            return $this->redirectToRoute('ra_vetting_sms_prove_possession', ['procedureId' => $procedureId]);
        }

        $form->addError(new FormError('ra.sms_send_challenge.send_sms_challenge_failed'));

        return [
            'phoneNumber' => $phoneNumber,
            'form'        => $form->createView()
        ];
    }

    /**
     * @Template
     * @param Request $request
     * @param string $procedureId
     * @return array|Response
     */
    public function provePossessionAction(Request $request, $procedureId)
    {
        $command = new VerifyPhoneNumberCommand();
        $form = $this
            ->createForm('ra_verify_phone_number', $command, ['procedureId' => $procedureId])
            ->handleRequest($request);

        if (!$form->isValid()) {
            return ['form' => $form->createView()];
        }

        if ($this->getVettingService()->verifyPhoneNumber($procedureId, $command)) {
            return $this->redirectToRoute(
                'ra_vetting_verify_identity',
                ['procedureId' => $procedureId]
            );
        }

        $form->addError(new FormError('ra.prove_phone_possession.proof_of_possession_failed'));

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
