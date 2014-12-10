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
use Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor\SendChallengeResult;
use Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor\VerificationResult;
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

        if (!$form->isValid()) {
            return ['form' => $form->createView()];
        }

        $result = $this->getVettingService()->sendSmsChallenge($procedureId, $command);

        switch ($result) {
            case SendChallengeResult::RESULT_CHALLENGE_SENT:
                return $this->redirectToRoute(
                    'ra_vetting_sms_prove_possession',
                    ['procedureId' => $procedureId, 'phoneNumber' => $command->phoneNumber]
                );
            case SendChallengeResult::RESULT_PHONE_NUMBER_DID_NOT_MATCH:
                $form->addError(new FormError('ra.sms_send_challenge.phone_number_mismatch'));
                break;
            case SendChallengeResult::RESULT_CHALLENGE_NOT_SENT:
                $form->addError(new FormError('ra.sms_send_challenge.send_sms_challenge_failed'));
                break;
            default:
                throw new \LogicException('Invalid send challenge result');
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Template
     * @param Request $request
     * @param string $procedureId
     * @param string $phoneNumber
     * @return array|Response
     */
    public function provePossessionAction(Request $request, $procedureId, $phoneNumber)
    {
        $command = new VerifyPhoneNumberCommand();
        $command->phoneNumber = $phoneNumber;

        $form = $this
            ->createForm('ra_verify_phone_number', $command, ['procedureId' => $procedureId])
            ->handleRequest($request);

        if (!$form->isValid()) {
            return ['form' => $form->createView()];
        }

        $result = $this->getVettingService()->verifyPhoneNumber($procedureId, $command);

        switch ($result) {
            case VerificationResult::RESULT_SUCCESS:
                return $this->redirectToRoute(
                    'ra_vetting_verify_identity',
                    ['procedureId' => $procedureId]
                );
            case VerificationResult::RESULT_CHALLENGE_MISMATCH:
                $form->addError(new FormError('ra.prove_phone_possession.proof_of_possession_failed'));
                break;
            case VerificationResult::RESULT_PHONE_NUMBER_DID_NOT_MATCH:
                $form->addError(new FormError('ra.prove_phone_possession.phone_number_mismatch'));
                break;
            default:
                throw new \LogicException('Invalid prove possession result');
        }

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
