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
use Surfnet\StepupMiddlewareClientBundle\Uuid\Uuid;
use Surfnet\StepupRa\RaBundle\Command\SendSmsChallengeCommand;
use Surfnet\StepupRa\RaBundle\Command\SendSmsCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyPhoneNumberCommand;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;
use Surfnet\StepupRa\RaBundle\Identity\Command\ProvePhonePossessionCommand;
use Surfnet\StepupRa\RaBundle\Identity\Command\VerifyEmailCommand;
use Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor\ChallengeStore;
use Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor\ProofOfPossessionResult;
use Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor\SendChallengeResult;
use Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor\VerificationResult;
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
     * @var ChallengeStore
     */
    private $challengeStore;

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
     * @param ChallengeStore $challengeStore
     * @param TranslatorInterface $translator
     * @param CommandService $commandService
     * @param string $originator
     */
    public function __construct(
        SmsService $smsService,
        ChallengeStore $challengeStore,
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
        $this->challengeStore = $challengeStore;
        $this->translator = $translator;
        $this->commandService = $commandService;
        $this->originator = $originator;
    }

    /**
     * @param SendSmsChallengeCommand $command
     * @return int One of the SendChallengeResult::RESULT_* constants.
     */
    public function sendChallenge(SendSmsChallengeCommand $command)
    {
        if ($command->recipient !== $command->procedure->getSecondFactor()->secondFactorIdentifier) {
            return SendChallengeResult::RESULT_PHONE_NUMBER_DID_NOT_MATCH;
        }

        $challenge = $this->challengeStore->generateChallenge();

        $body = $this->translator->trans('ra.vetting.sms.challenge_body', ['%challenge%' => $challenge]);

        $smsCommand = new SendSmsCommand();
        $smsCommand->recipient = $command->recipient;
        $smsCommand->originator = $this->originator;
        $smsCommand->body = $body;
        $smsCommand->identity = $command->identity;
        $smsCommand->institution = $command->institution;

        return $this->smsService->sendSms($smsCommand)
            ? SendChallengeResult::RESULT_CHALLENGE_SENT
            : SendChallengeResult::RESULT_CHALLENGE_NOT_SENT;
    }

    /**
     * @param VerifyPhoneNumberCommand $command
     * @return int One of the VerificationResult::RESULT_* constants.
     */
    public function verifyPossession(VerifyPhoneNumberCommand $command)
    {
        if ($command->phoneNumber !== $command->procedure->getSecondFactor()->secondFactorIdentifier) {
            return VerificationResult::RESULT_PHONE_NUMBER_DID_NOT_MATCH;
        }

        if (!$this->challengeStore->verifyChallenge($command->challenge)) {
            return VerificationResult::RESULT_CHALLENGE_MISMATCH;
        }

        $command->procedure->verifySecondFactorIdentifier($command->phoneNumber);

        return VerificationResult::RESULT_SUCCESS;
    }
}
