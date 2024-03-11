<?php

declare(strict_types=1);

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
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class VettingProcedure
{
    private string $id;
    private string $authorityId;

    private ?string $registrationCode = null;

    private VerifiedSecondFactor $secondFactor;

    private ?string $inputSecondFactorIdentifier = null;

    private ?string $documentNumber = null;

    private ?bool $identityVerified = null;

    private ?bool $skipProvePossession = null;

    final private function __construct()
    {
    }

    public static function start(
        string               $id,
        string               $authorityId,
        string               $registrationCode,
        VerifiedSecondFactor $secondFactor,
        bool                 $skipProvePossession,
    ): VettingProcedure {
        $procedure = new self();
        $procedure->id = $id;
        $procedure->authorityId = $authorityId;
        $procedure->registrationCode = $registrationCode;
        $procedure->secondFactor = $secondFactor;
        $procedure->skipProvePossession = $skipProvePossession;

        return $procedure;
    }

    /**
     * @throws DomainException
     */
    public function verifySecondFactorIdentifier(string $secondFactorIdentifier): void
    {
        if (!$this->isReadyForSecondFactorToBeVerified()) {
            throw new DomainException(
                'Second factor is not yet ready for verification of second factor, ' .
                'it has already been verified or the registration code is unknown.',
            );
        }

        if ($secondFactorIdentifier !== $this->secondFactor->secondFactorIdentifier) {
            throw new DomainException("Input second factor identifier doesn't match expected second factor identifier");
        }

        $this->inputSecondFactorIdentifier = $secondFactorIdentifier;
    }

    private function isReadyForSecondFactorToBeVerified(): bool
    {
        return (
            $this->registrationCode !== null &&
            $this->registrationCode !== '' &&
            $this->registrationCode !== '0'
        );
    }

    /**
     * @throws DomainException
     */
    public function verifyIdentity(string $documentNumber, bool $identityVerified): void
    {
        if (!$this->isReadyForIdentityVerification()) {
            throw new DomainException(
                'Second factor is not yet ready for verification of its Identity; ' .
                'verify the registrant has the same second factor as used during the registration process.',
            );
        }

        if ($documentNumber === '' || $documentNumber === '0') {
            throw new InvalidArgumentException('Document number may not be empty.');
        }

        if (!$identityVerified) {
            throw new DomainException("The registrant's identity must have been confirmed by the RA.");
        }

        $this->documentNumber = $documentNumber;
        $this->identityVerified = true;
    }

    private function isReadyForIdentityVerification(): bool
    {
        return $this->isPossessionProvenOrCanItBeSkipped() && !empty($this->registrationCode);
    }

    private function isPossessionProvenOrCanItBeSkipped(): bool
    {
        return (
            $this->inputSecondFactorIdentifier === $this->secondFactor->secondFactorIdentifier
            || $this->skipProvePossession
        );
    }

    /**
     * @throws DomainException
     */
    public function vet(): void
    {
        if (!$this->isReadyForVetting()) {
            throw new DomainException(
                'Second factor is not yet ready for verification of its Identity; ' .
                'verify the registrant has the same second factor as used during the registration process, ' .
                "and verify the registrant's identity.",
            );
        }
    }

    private function isReadyForVetting(): bool
    {
        return $this->isPossessionProvenOrCanItBeSkipped()
            && !empty($this->registrationCode)
            && !empty($this->documentNumber)
            && $this->identityVerified === true;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSecondFactor(): VerifiedSecondFactor
    {
        return $this->secondFactor;
    }

    public function getAuthorityId(): string
    {
        return $this->authorityId;
    }

    public function getRegistrationCode(): ?string
    {
        return $this->registrationCode;
    }

    public function getInputSecondFactorIdentifier(): ?string
    {
        return $this->inputSecondFactorIdentifier;
    }

    public function getDocumentNumber(): ?string
    {
        return $this->documentNumber;
    }

    public function isIdentityVerified(): ?bool
    {
        return $this->identityVerified;
    }

    public function isProvePossessionSkippable(): ?bool
    {
        return $this->skipProvePossession;
    }
}
