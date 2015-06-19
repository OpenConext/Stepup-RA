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
use Surfnet\StepupBundle\Value\YubikeyOtp;
use Surfnet\StepupBundle\Value\YubikeyPublicId;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyOtpCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyPublicIdCommand;
use Surfnet\StepupRa\RaBundle\Service\YubikeySecondFactor\VerificationResult;

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

        $verificationResult = $this->yubikeyService->verify($verifyOtpCommand);

        if (YubikeyOtp::isValid($command->otp)) {
            $otp      = YubikeyOtp::fromString($command->otp);
            $publicId = YubikeyPublicId::fromOtp($otp);
        } else {
            $publicId = null;
        }

        if ($verificationResult->isServerError()) {
            return new VerificationResult(VerificationResult::RESULT_OTP_VERIFICATION_FAILED, $publicId);
        } elseif ($verificationResult->isClientError()) {
            return new VerificationResult(VerificationResult::RESULT_OTP_INVALID, $publicId);
        }

        if ($publicId->getYubikeyPublicId() !== $command->expectedPublicId) {
            $this->logger->notice(
                'Yubikey used by registrant during vetting did not match the one used during registration.'
            );

            return new VerificationResult(VerificationResult::RESULT_PUBLIC_ID_DID_NOT_MATCH, $publicId->getYubikeyPublicId());
        }

        $this->logger->info(
            'Yubikey used by registrant during vetting matches the one used during registration.'
        );

        return new VerificationResult(VerificationResult::RESULT_PUBLIC_ID_MATCHED, $publicId->getYubikeyPublicId());
    }
}
