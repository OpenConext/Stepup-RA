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
    private $id;

    /**
     * @var string
     */
    private $authorityId;

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
     * @var boolean|null
     */
    private $skipProvePossession;

    /**
     * @var boolean|null
     */
    private $vetted;

    final private function __construct()
    {
    }

    /**
     * @param string $id
     * @param string $authorityId
     * @param string $registrationCode
     * @param VerifiedSecondFactor $secondFactor
     * @param bool $skipProvePossession
     * @return self
     */
    public static function start(
        string $id,
        string $authorityId,
        string $registrationCode,
        VerifiedSecondFactor $secondFactor,
        bool $skipProvePossession
    ): self {
        $procedure = new self();
        $procedure->id = $id;
        $procedure->authorityId = $authorityId;
        $procedure->registrationCode = $registrationCode;
        $procedure->secondFactor = $secondFactor;
        $procedure->skipProvePossession = $skipProvePossession;

        return $procedure;
    }

    /**
     * @param string $secondFactorIdentifier
     * @return void
     * @throws DomainException
     */
    public function verifySecondFactorIdentifier(string $secondFactorIdentifier): void
    {
        if (!$this->isReadyForSecondFactorToBeVerified()) {
            throw new DomainException(
                'Second factor is not yet ready for verification of second factor, ' .
                'it has already been verified or the registration code is unknown.'
            );
        }

        if ($secondFactorIdentifier !== $this->secondFactor->secondFactorIdentifier) {
            throw new DomainException("Input second factor identifier doesn't match expected second factor identifier");
        }

        $this->inputSecondFactorIdentifier = $secondFactorIdentifier;
    }

    /**
     * @param string $documentNumber
     * @param bool $identityVerified
     * @return void
     * @throws DomainException
     */
    public function verifyIdentity(string $documentNumber, bool $identityVerified): void
    {
        if (!$this->isReadyForIdentityVerification()) {
            throw new DomainException(
                'Second factor is not yet ready for verification of its Identity; ' .
                'verify the registrant has the same second factor as used during the registration process.'
            );
        }

        if (!is_string($documentNumber)) {
            throw InvalidArgumentException::invalidType('string', 'documentNumber', $documentNumber);
        }

        if (empty($documentNumber)) {
            throw new InvalidArgumentException('Document number may not be empty.');
        }

        if ($identityVerified !== true) {
            throw new DomainException("The registrant's identity must have been confirmed by the RA.");
        }

        $this->documentNumber = $documentNumber;
        $this->identityVerified = true;
    }

    /**
     * @return void
     * @throws DomainException
     */
    public function vet(): void
    {
        if (!$this->isReadyForVetting()) {
            throw new DomainException(
                'Second factor is not yet ready for verification of its Identity; ' .
                'verify the registrant has the same second factor as used during the registration process, '.
                "and verify the registrant's identity."
            );
        }

        $this->vetted = true;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return VerifiedSecondFactor
     */
    public function getSecondFactor(): VerifiedSecondFactor
    {
        return $this->secondFactor;
    }

    /**
     * @return string
     */
    public function getAuthorityId(): string
    {
        return $this->authorityId;
    }

    /**
     * @return null|string
     */
    public function getRegistrationCode(): ?string
    {
        return $this->registrationCode;
    }

    /**
     * @return null|string
     */
    public function getInputSecondFactorIdentifier(): ?string
    {
        return $this->inputSecondFactorIdentifier;
    }

    /**
     * @return null|string
     */
    public function getDocumentNumber(): ?string
    {
        return $this->documentNumber;
    }

    /**
     * @return bool|null
     */
    public function isIdentityVerified(): ?bool
    {
        return $this->identityVerified;
    }

    /**
     * @return bool|null
     */
    public function isProvePossessionSkippable(): ?bool
    {
        return $this->skipProvePossession;
    }

    /**
     * @return bool
     */
    private function isReadyForSecondFactorToBeVerified(): bool
    {
        return !empty($this->registrationCode);
    }

    /**
     * @return bool
     */
    private function isReadyForIdentityVerification(): bool
    {
        return $this->isPossessionProvenOrCanItBeSkipped() && !empty($this->registrationCode);
    }

    /**
     * @return bool
     */
    private function isReadyForVetting(): bool
    {
        return $this->isPossessionProvenOrCanItBeSkipped()
            && !empty($this->registrationCode)
            && !empty($this->documentNumber)
            && $this->identityVerified === true;
    }

    /**
     * @return bool
     */
    private function isPossessionProvenOrCanItBeSkipped(): bool
    {
        return (
            $this->inputSecondFactorIdentifier === $this->secondFactor->secondFactorIdentifier
            || $this->skipProvePossession
        );
    }
}
