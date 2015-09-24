<?php

/**
 * Copyright 2015 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Service\U2f;

final class AuthenticationVerificationResult
{
    const STATUS_SUCCESS = 'SUCCESS';

    /**
     * The API behaved in an unexpected manner.
     */
    const STATUS_API_ERROR = 'API_ERROR';

    /**
     * No registration with the given key handle is known.
     */
    const STATUS_UNKNOWN_KEY_HANDLE = 'UNKNOWN_KEY_HANDLE';

    /**
     * Device responded with an error.
     */
    const STATUS_DEVICE_ERROR = 'DEVICE_ERROR';

    /**
     * The response challenge did not match the request challenge.
     */
    const STATUS_REQUEST_RESPONSE_MISMATCH = 'REQUEST_RESPONSE_MISMATCH';

    /**
     * The response was signed by another party than the device, indicating it was tampered with.
     */
    const STATUS_RESPONSE_NOT_SIGNED_BY_DEVICE = 'RESPONSE_NOT_SIGNED_BY_DEVICE';

    /**
     * The decoding of the device's public key failed.
     */
    const STATUS_PUBLIC_KEY_DECODING_FAILED = 'PUBLIC_KEY_DECODING_FAILED';

    /**
     * A message's AppID didn't match the server's
     */
    const STATUS_APP_ID_MISMATCH = 'APP_ID_MISMATCH';

    /**
     * @var string
     */
    private $status;

    public static function success()
    {
        return new self(self::STATUS_SUCCESS);
    }

    /**
     * @param string $status
     * @return AuthenticationVerificationResult
     */
    public static function error($status)
    {
        return new self($status);
    }

    public static function apiError()
    {
        return new self(self::STATUS_API_ERROR);
    }

    private function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function wasSuccessful()
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * @return bool
     */
    public function didDeviceReportAnyError()
    {
        return $this->status === self::STATUS_DEVICE_ERROR;
    }
}
