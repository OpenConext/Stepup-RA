<?php

/**
 * Copyright 2024 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Tests\TestDouble\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Log\LoggerInterface;
use Surfnet\StepupBundle\Value\YubikeyOtp;
use Surfnet\StepupBundle\Value\YubikeyPublicId;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyOtpCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyPublicIdCommand;
use Surfnet\StepupRa\RaBundle\Service\YubikeySecondFactor\VerificationResult;
use Surfnet\StepupRa\RaBundle\Service\YubikeySecondFactorServiceInterface;

class YubikeySecondFactorService implements YubikeySecondFactorServiceInterface
{
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function verifyYubikeyPublicId(VerifyYubikeyPublicIdCommand $command): VerificationResult
    {
        $this->logger->critical(
            'Using the TestDouble yubikey YubikeySecondFactorService::verifyYubikeyPublicId method. '.
            'Always returns a positive result. Be careful, only to use this during test or development!'
        );
        $publicId = new YubikeyPublicId('09999999');
        return new VerificationResult(VerificationResult::RESULT_PUBLIC_ID_MATCHED, $publicId);
    }
}
