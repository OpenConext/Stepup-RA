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

namespace Surfnet\StepupRa\RaBundle;

use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\VerifiedSecondFactor;
use Surfnet\StepupMiddlewareClientBundle\Uuid\Uuid;
use Surfnet\StepupRa\RaBundle\Exception\DomainException;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;

/**
 * @SuppressWarnings(PHPMD.UnusedPrivateFields)
 */
class VettingProcedure
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string|null
     */
    private $registrationCode;

    /**
     * @var VerifiedSecondFactor
     */
    private $secondFactor;

    /**
     * @var string|null
     */
    private $inputSecondFactorIdentifier;

    /**
     * @var string|null
     */
    private $documentNumber;

    /**
     * @var boolean|null
     */
    private $identityVerified;

    /**
     * @param string $registrationCode
     * @param VerifiedSecondFactor $secondFactor
     * @return self
     */
    public static function start($registrationCode, VerifiedSecondFactor $secondFactor)
    {
        $procedure = new self();
        $procedure->uuid = Uuid::generate();
        $procedure->registrationCode = $registrationCode;
        $procedure->secondFactor = $secondFactor;

        return $procedure;
    }

    final private function __construct()
    {
    }

    /**
     * @param string $secondFactorIdentifier
     */
    public function verifySecondFactorIdentifier($secondFactorIdentifier)
    {
        if ($secondFactorIdentifier !== $this->secondFactor->secondFactorIdentifier) {
            throw new DomainException("Input second factor identifier doesn't match expected second factor identifier");
        }

        $this->inputSecondFactorIdentifier = $secondFactorIdentifier;
    }

    public function verifyIdentity($documentNumber)
    {
        if (!is_string($documentNumber)) {
            throw InvalidArgumentException::invalidType('string', 'documentNumber', $documentNumber);
        }

        if (empty($documentNumber)) {
            throw new InvalidArgumentException('Document number may not be empty.');
        }

        $this->documentNumber = $documentNumber;
        $this->identityVerified = true;
    }

    /**
     * @return bool
     */
    public function isReadyForIdentityVerification()
    {
        return $this->inputSecondFactorIdentifier === $this->secondFactor->secondFactorIdentifier;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return VerifiedSecondFactor
     */
    public function getSecondFactor()
    {
        return $this->secondFactor;
    }
}
