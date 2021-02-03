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

use Surfnet\StepupU2fBundle\Dto\SignRequest;

final class SignRequestCreationResult
{
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_UNKNOWN_KEY_HANDLE = 'UNKNOWN_KEY_HANDLE';
    const STATUS_API_ERROR = 'API_ERROR';

    /**
     * @var string
     */
    private $status;

    /**
     * @var SignRequest
     */
    private $signRequest;

    /**
     * @param SignRequest $signRequest
     * @return SignRequestCreationResult
     */
    public static function success(SignRequest $signRequest)
    {
        $result = new self(self::STATUS_SUCCESS);
        $result->signRequest = $signRequest;

        return $result;
    }

    /**
     * @return SignRequestCreationResult
     */
    public static function unknownKeyHandle()
    {
        return new self(self::STATUS_UNKNOWN_KEY_HANDLE);
    }

    /**
     * @return SignRequestCreationResult
     */
    public static function apiError()
    {
        return new self(self::STATUS_API_ERROR);
    }

    private function __construct(string $status)
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
     * @return mixed
     */
    public function getSignRequest()
    {
        return $this->signRequest;
    }
}
