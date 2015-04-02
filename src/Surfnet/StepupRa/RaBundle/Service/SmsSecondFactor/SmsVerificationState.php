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

namespace Surfnet\StepupRa\RaBundle\Service\SmsSecondFactor;

use DateInterval;
use Surfnet\StepupBundle\Security\OtpGenerator;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;

final class SmsVerificationState
{
    /**
     * @var DateInterval
     */
    private $expiryInterval;

    /**
     * @var Otp[]
     */
    private $otps;

    /**
     * @param DateInterval $expiryInterval
     */
    public function __construct(DateInterval $expiryInterval)
    {
        $this->expiryInterval = $expiryInterval;
        $this->otps = [];
    }

    /**
     * @param string $phoneNumber
     * @return string The generated OTP string.
     */
    public function requestNewOtp($phoneNumber)
    {
        if (!is_string($phoneNumber) || empty($phoneNumber)) {
            throw InvalidArgumentException::invalidType('string', 'phoneNumber', $phoneNumber);
        }

        $this->otps = array_filter($this->otps, function (Otp $otp) use ($phoneNumber) {
            return $otp->hasPhoneNumber($phoneNumber);
        });

        $otp = OtpGenerator::generate();
        $this->otps[] = Otp::create($otp, $phoneNumber, $this->expiryInterval);

        return $otp;
    }

    /**
     * @param string $userOtp
     * @return OtpVerification
     */
    public function verify($userOtp)
    {
        if (!is_string($userOtp)) {
            throw InvalidArgumentException::invalidType('string', 'userOtp', $userOtp);
        }

        foreach ($this->otps as $otp) {
            $verification = $otp->verify($userOtp);

            if ($verification->didOtpMatch()) {
                return $verification;
            }
        }

        return OtpVerification::noMatch();
    }
}
