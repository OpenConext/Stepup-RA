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

namespace Surfnet\StepupRa\RaBundle\Service;

use Surfnet\StepupMiddlewareClientBundle\Service\CommandService;
use Surfnet\StepupRa\RaBundle\Command\SendSmsChallengeCommand;
use Surfnet\StepupRa\RaBundle\Command\SendSmsCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyPhoneNumberCommand;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;
use Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor\OtpVerification;
use Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor\SmsVerificationStateHandler;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SmsSecondFactorService
{
    /**
     * @var SmsService
     */
    private $smsService;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor\SmsVerificationStateHandler
     */
    private $smsVerificationStateHandler;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CommandService
     */
    private $commandService;

    /**
     * @var string
     */
    private $originator;

    /**
     * @param SmsService $smsService
     * @param SmsVerificationStateHandler $smsVerificationStateHandler
     * @param TranslatorInterface $translator
     * @param CommandService $commandService
     * @param string $originator
     */
    public function __construct(
        SmsService $smsService,
        SmsVerificationStateHandler $smsVerificationStateHandler,
        TranslatorInterface $translator,
        CommandService $commandService,
        $originator
    ) {
        if (!is_string($originator)) {
            throw InvalidArgumentException::invalidType('string', 'originator', $originator);
        }

        if (!preg_match('~^[a-z0-9]{1,11}$~i', $originator)) {
            throw new InvalidArgumentException(
                'Invalid SMS originator given: may only contain alphanumerical characters.'
            );
        }

        $this->smsService = $smsService;
        $this->smsVerificationStateHandler = $smsVerificationStateHandler;
        $this->translator = $translator;
        $this->commandService = $commandService;
        $this->originator = $originator;
    }

    /**
     * @param SendSmsChallengeCommand $command
     * @return bool
     */
    public function sendChallenge(SendSmsChallengeCommand $command)
    {
        $challenge = $this->smsVerificationStateHandler->requestNewOtp($command->phoneNumber);

        $body = $this->translator->trans('ra.vetting.sms.challenge_body', ['%challenge%' => $challenge]);

        $smsCommand = new SendSmsCommand();
        $smsCommand->recipient = $command->phoneNumber;
        $smsCommand->originator = $this->originator;
        $smsCommand->body = $body;
        $smsCommand->identity = $command->identity;
        $smsCommand->institution = $command->institution;

        return $this->smsService->sendSms($smsCommand);
    }

    /**
     * @param VerifyPhoneNumberCommand $command
     * @return OtpVerification
     */
    public function verifyPossession(VerifyPhoneNumberCommand $command)
    {
        return $this->smsVerificationStateHandler->verify($command->challenge);
    }
}
