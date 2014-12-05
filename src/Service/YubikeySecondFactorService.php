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

use Psr\Log\LoggerInterface;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyOtpCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyPublicIdCommand;
use Surfnet\StepupRa\RaBundle\Service\YubikeySecondFactor\VerificationResult;
use Surfnet\StepupRa\RaBundle\Value\Otp;

class YubikeySecondFactorService
{
    /**
     * @var YubikeyService
     */
    private $yubikeyService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param YubikeyService $yubikeyService
     * @param LoggerInterface $logger
     */
    public function __construct(YubikeyService $yubikeyService, LoggerInterface $logger)
    {
        $this->yubikeyService = $yubikeyService;
        $this->logger = $logger;
    }

    /**
     * @param VerifyYubikeyPublicIdCommand $command
     * @return VerificationResult
     */
    public function verifyYubikeyPublicId(VerifyYubikeyPublicIdCommand $command)
    {
        $verifyOtpCommand = new VerifyYubikeyOtpCommand();
        $verifyOtpCommand->otp = $command->otp;
        $verifyOtpCommand->identityId = $command->identityId;
        $verifyOtpCommand->institution = $command->institution;

        $publicId = Otp::isValid($command->otp) ? Otp::fromString($command->otp)->publicId : null;

        if (!$this->yubikeyService->verify($verifyOtpCommand)) {
            return new VerificationResult(VerificationResult::RESULT_OTP_VERIFICATION_FAILED, $publicId);
        }

        if ($publicId !== $command->publicId) {
            $this->logger->notice(
                'Yubikey used by registrant during vetting did not match the one used during registration.'
            );

            return new VerificationResult(VerificationResult::RESULT_PUBLIC_ID_DID_NOT_MATCH, $publicId);
        }

        $this->logger->info(
            'Yubikey used by registrant during vetting matches the one used during registration.'
        );

        return new VerificationResult(VerificationResult::RESULT_PUBLIC_ID_MATCHED, $publicId);
    }
}
