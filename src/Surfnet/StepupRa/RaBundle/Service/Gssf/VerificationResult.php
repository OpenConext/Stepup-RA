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

namespace Surfnet\StepupRa\RaBundle\Service\Gssf;

use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;

final class VerificationResult
{
    /**
     * @var boolean
     */
    private $verificationSucceeded;

    /**
     * @var string|null
     */
    private $procedureId;

    /**
     * @param string $procedureId
     */
    public static function verificationSucceeded($procedureId): VerificationResult
    {
        if (!is_string($procedureId)) {
            throw InvalidArgumentException::invalidType('string', 'procedureId', $procedureId);
        }

        $result = new self();
        $result->verificationSucceeded = true;
        $result->procedureId = $procedureId;

        return $result;
    }

    public static function noSuchProcedure(): VerificationResult
    {
        $result = new self();
        $result->verificationSucceeded = false;

        return $result;
    }

    /**
     * @param string $procedureId
     */
    public static function verificationFailed($procedureId): VerificationResult
    {
        if (!is_string($procedureId)) {
            throw InvalidArgumentException::invalidType('string', 'procedureId', $procedureId);
        }

        $result = new self();
        $result->verificationSucceeded = false;
        $result->procedureId = $procedureId;

        return $result;
    }

    public function isSuccess(): bool
    {
        return $this->verificationSucceeded && $this->procedureId;
    }

    public function didVerificationSucceed(): bool
    {
        return $this->verificationSucceeded;
    }

    /**
     * @return null|string NULL if no procedure ID was known for given SAML request ID.
     */
    public function getProcedureId(): ?string
    {
        return $this->procedureId;
    }
}
