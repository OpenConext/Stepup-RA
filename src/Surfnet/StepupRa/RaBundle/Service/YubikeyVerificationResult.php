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

class YubikeyVerificationResult
{
    /**
     * @var bool
     */
    private $clientError;

    /**
     * @var bool
     */
    private $serverError;

    /**
     * @param bool $clientError
     * @param bool $serverError
     */
    public function __construct($clientError, $serverError)
    {
        $this->clientError = $clientError;
        $this->serverError = $serverError;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return !$this->clientError && !$this->serverError;
    }

    /**
     * @return boolean
     */
    public function isClientError()
    {
        return $this->clientError;
    }

    /**
     * @return boolean
     */
    public function isServerError()
    {
        return $this->serverError;
    }
}
