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
use Surfnet\StepupBundle\Command\SendSmsChallengeCommand;
use Surfnet\StepupBundle\Command\VerifyPhoneNumberCommand;
use Surfnet\StepupBundle\Command\VerifyPossessionOfPhoneCommand;
use Surfnet\StepupBundle\Value\PhoneNumber\InternationalPhoneNumber;
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
        $this->denyAccessUnlessGranted(['ROLE_RA']);

        $logger = $this->get('ra.procedure_logger')->forProcedure($procedureId);
        $logger->notice('Received request for Send SMS Challenge page');

        $command = new SendSmsChallengeCommand();
        $form = $this->createForm('ra_send_sms_challenge', $command)->handleRequest($request);

        $vettingService = $this->getVettingService();
        $phoneNumber = InternationalPhoneNumber::fromStringFormat(
            $vettingService->getSecondFactorIdentifier($procedureId)
        );

        $otpRequestsRemaining = $vettingService->getSmsOtpRequestsRemainingCount();
        $maximumOtpRequests = $vettingService->getSmsMaximumOtpRequestsCount();
        $viewVariables = ['otpRequestsRemaining' => $otpRequestsRemaining, 'maximumOtpRequests' => $maximumOtpRequests];

        if (!$form->isValid()) {
            $logger->notice('Form has not been submitted, not sending SMS, rendering Send SMS Challenge page');

            return array_merge(
                $viewVariables,
                ['phoneNumber' => $phoneNumber, 'form' => $form->createView()]
            );
        }

        $logger->notice('Sending of SMS Challenge has been requested, sending OTP via SMS');
        if ($vettingService->sendSmsChallenge($procedureId, $command)) {
            $logger->notice(
                'SMS Challenge successfully sent, redirecting to Proof of Possession page to verify challenge'
            );

            return $this->redirectToRoute('ra_vetting_sms_prove_possession', ['procedureId' => $procedureId]);
        }

        $form->addError(new FormError('ra.sms_send_challenge.send_sms_challenge_failed'));

        $logger->notice(
            'SMS Challenge could not be sent, added error to page to notify user and re-rendering send challenge page'
        );

        return array_merge(
            $viewVariables,
            ['phoneNumber' => $phoneNumber, 'form' => $form->createView()]
        );
    }

    /**
     * @Template
     * @param Request $request
     * @param string $procedureId
     * @return array|Response
     */
    public function provePossessionAction(Request $request, $procedureId)
    {
        $this->denyAccessUnlessGranted(['ROLE_RA']);
        $logger = $this->get('ra.procedure_logger')->forProcedure($procedureId);

        $logger->notice('Received request for Proof of Possession of SMS Second Factor page');

        $command = new VerifyPossessionOfPhoneCommand();
        $form = $this
            ->createForm('ra_verify_phone_number', $command, ['procedureId' => $procedureId])
            ->handleRequest($request);

        if (!$form->isValid()) {
            $logger->notice(
                'SMS OTP was not submitted through form, rendering Proof of Possession of SMS Second Factor page'
            );

            return ['form' => $form->createView()];
        }

        $logger->notice('SMS OTP has been entered, attempting to verify Proof of Possession');
        $verification = $this->getVettingService()->verifyPhoneNumber($procedureId, $command);
        if ($verification->wasSuccessful()) {
            $logger->notice('SMS OTP was valid, Proof of Possession given, redirecting to Identity Vetting page');

            return $this->redirectToRoute(
                'ra_vetting_verify_identity',
                ['procedureId' => $procedureId]
            );
        } elseif ($verification->didOtpExpire()) {
            $form->addError(new FormError('ra.prove_phone_possession.challenge_expired'));
        } elseif ($verification->wasAttemptedTooManyTimes()) {
            $form->addError(new FormError('ra.prove_phone_possession.too_many_attempts'));
        } else {
            $form->addError(new FormError('ra.prove_phone_possession.challenge_response_incorrect'));
        }

        $logger->notice('SMS OTP verification failed - Proof of Possession denied, added error to form');

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
