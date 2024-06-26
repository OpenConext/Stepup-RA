<?php

/**
 * Copyright 2015 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Service\YubikeySecondFactor;

use Surfnet\StepupBundle\Value\YubikeyPublicId;
use Surfnet\StepupRa\RaBundle\Exception\DomainException;

class VerificationResult
{
    final public const RESULT_PUBLIC_ID_MATCHED = 0;
    final public const RESULT_PUBLIC_ID_DID_NOT_MATCH = 1;
    final public const RESULT_OTP_VERIFICATION_FAILED = 2;
    final public const RESULT_OTP_INVALID = 3;

    /**
     * @var int One of the RESULT constants.
     */
    private $result;

    /**
     * @param int $result
     * @param YubikeyPublicId|null $publicId
     */
    public function __construct($result, private readonly ?YubikeyPublicId $publicId = null)
    {
        $acceptableResults = [
            self::RESULT_PUBLIC_ID_MATCHED,
            self::RESULT_PUBLIC_ID_DID_NOT_MATCH,
            self::RESULT_OTP_VERIFICATION_FAILED,
            self::RESULT_OTP_INVALID,
        ];

        if (!in_array($result, $acceptableResults)) {
            throw new DomainException('Public ID verification result is not one of the RESULT constants.');
        }

        $this->result = $result;
    }

    public function didPublicIdMatch(): bool
    {
        return $this->result === self::RESULT_PUBLIC_ID_MATCHED && $this->publicId instanceof YubikeyPublicId;
    }

    public function wasOtpInvalid(): bool
    {
        return $this->result === self::RESULT_OTP_INVALID;
    }

    public function didOtpVerificationFail(): bool
    {
        return $this->result === self::RESULT_OTP_VERIFICATION_FAILED;
    }

    public function getPublicId(): ?YubikeyPublicId
    {
        return $this->publicId;
    }
}
