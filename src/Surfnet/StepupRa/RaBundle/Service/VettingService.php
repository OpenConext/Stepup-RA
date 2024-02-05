<?php

declare(strict_types = 1);

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

use DateInterval;
use RuntimeException;
use Surfnet\StepupBundle\Command\SendSmsChallengeCommand;
use Surfnet\StepupBundle\Command\VerifyPossessionOfPhoneCommand;
use Surfnet\StepupBundle\Service\SecondFactorTypeService;
use Surfnet\StepupBundle\Service\SmsSecondFactor\OtpVerification;
use Surfnet\StepupBundle\Service\SmsSecondFactorServiceInterface;
use Surfnet\StepupBundle\Value\PhoneNumber\InternationalPhoneNumber;
use Surfnet\StepupBundle\Value\SecondFactorType;
use Surfnet\StepupBundle\Value\VettingType;
use Surfnet\StepupMiddlewareClient\Service\ExecutionResult;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\SecondFactorService;
use Surfnet\StepupMiddlewareClientBundle\Identity\Command\VetSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\Command\StartVettingProcedureCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyIdentityCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyPublicIdCommand;
use Surfnet\StepupRa\RaBundle\Exception\DomainException;
use Surfnet\StepupRa\RaBundle\Exception\LoaTooLowException;
use Surfnet\StepupRa\RaBundle\Exception\UnknownVettingProcedureException;
use Surfnet\StepupRa\RaBundle\Repository\VettingProcedureRepository;
use Surfnet\StepupRa\RaBundle\Service\Gssf\VerificationResult as GssfVerificationResult;
use Surfnet\StepupRa\RaBundle\Value\DateTime;
use Surfnet\StepupRa\RaBundle\VettingProcedure;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class VettingService
{
    final public const REGISTRATION_CODE_EXPIRED_ERROR =
        'Surfnet\Stepup\Exception\DomainException: Cannot vet second factor, the registration window is closed.';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly SmsSecondFactorServiceInterface $smsSecondFactorService,
        private readonly YubikeySecondFactorService $yubikeySecondFactorService,
        private readonly GssfService $gssfService,
        private readonly CommandService $commandService,
        private readonly VettingProcedureRepository $vettingProcedureRepository,
        private readonly TranslatorInterface $translator,
        private readonly IdentityService $identityService,
        private readonly SecondFactorTypeService $secondFactorTypeService,
        private readonly SecondFactorService $secondFactorService,
    ) {
    }

    public function isLoaSufficientToStartProcedure(StartVettingProcedureCommand $command): bool
    {
        $secondFactorType = new SecondFactorType($command->secondFactor->type);

        return $this->secondFactorTypeService->isSatisfiedBy(
            $secondFactorType,
            $command->authorityLoa,
            new VettingType(VettingType::TYPE_ON_PREMISE),
        );
    }

    public function isExpiredRegistrationCode(StartVettingProcedureCommand $command): bool
    {
        return DateTime::now()->comesAfter(
            new DateTime(
                $command->secondFactor->registrationRequestedAt
                    ->add(new DateInterval('P14D'))
                    ->setTime(23, 59, 59),
            ),
        );
    }

    public function startProcedure(StartVettingProcedureCommand $command): string
    {
        $this->smsSecondFactorService->clearSmsVerificationState($command->secondFactor->id);

        if (!$this->isLoaSufficientToStartProcedure($command)) {
            throw new LoaTooLowException(
                sprintf(
                    "Registration authority has LoA '%s', which is not enough to allow vetting of a '%s' second factor",
                    $command->authorityLoa,
                    $command->secondFactor->type,
                ),
            );
        }

        $provePossessionSkipped = $this->secondFactorService->getVerifiedCanSkipProvePossession($command->secondFactor->id);

        $procedure = VettingProcedure::start(
            $command->secondFactor->id,
            $command->authorityId,
            $command->registrationCode,
            $command->secondFactor,
            $provePossessionSkipped,
        );

        $this->vettingProcedureRepository->store($procedure);

        return $procedure->getId();
    }

    /**
     * @throws UnknownVettingProcedureException
     */
    public function cancelProcedure(string $procedureId): void
    {
        $procedure = $this->vettingProcedureRepository->retrieve($procedureId);

        if (!$procedure) {
            throw new UnknownVettingProcedureException(
                sprintf("No vetting procedure with id '%s' is known.", $procedureId),
            );
        }

        $this->vettingProcedureRepository->remove($procedureId);
    }

    public function getSmsOtpRequestsRemainingCount(string $secondFactorId): int
    {
        return $this->smsSecondFactorService->getOtpRequestsRemainingCount($secondFactorId);
    }

    public function getSmsMaximumOtpRequestsCount(): int
    {
        return $this->smsSecondFactorService->getMaximumOtpRequestsCount();
    }

    /**
     * @throws UnknownVettingProcedureException
     * @throws RuntimeException
     */
    public function sendSmsChallenge(string $procedureId, SendSmsChallengeCommand $command): bool
    {
        $procedure = $this->getProcedure($procedureId);

        $phoneNumber = InternationalPhoneNumber::fromStringFormat(
            $procedure->getSecondFactor()->secondFactorIdentifier,
        );

        $identity = $this->identityService->findById($procedure->getSecondFactor()->identityId);

        if (!$identity) {
            throw new RuntimeException("Second factor is coupled to an identity that doesn't exist");
        }

        $command->phoneNumber = $phoneNumber;
        $command->body = $this->translator->trans(
            'ra.vetting.sms.challenge_body',
            [],
            'messages',
            $identity->preferredLocale,
        );
        $command->identity = $procedure->getSecondFactor()->identityId;
        $command->institution = $procedure->getSecondFactor()->institution;
        $command->secondFactorId = $procedure->getSecondFactor()->id;

        return $this->smsSecondFactorService->sendChallenge($command);
    }

    /**
     * @throws UnknownVettingProcedureException
     * @throws DomainException
     */
    public function verifyPhoneNumber(string $procedureId, VerifyPossessionOfPhoneCommand $command): OtpVerification
    {
        $procedure = $this->getProcedure($procedureId);
        $command->secondFactorId = $procedure->getSecondFactor()->id;

        $verification = $this->smsSecondFactorService->verifyPossession($command);

        if (!$verification->wasSuccessful()) {
            return $verification;
        }

        $procedure->verifySecondFactorIdentifier($verification->getPhoneNumber());
        $this->vettingProcedureRepository->store($procedure);

        return $verification;
    }

    public function verifyYubikeyPublicId(
        string $procedureId,
        VerifyYubikeyPublicIdCommand $command,
    ): YubikeySecondFactor\VerificationResult {
        $procedure = $this->getProcedure($procedureId);

        $command->expectedPublicId = $procedure->getSecondFactor()->secondFactorIdentifier;
        $command->identityId = $procedure->getSecondFactor()->identityId;
        $command->institution = $procedure->getSecondFactor()->institution;

        $result = $this->yubikeySecondFactorService->verifyYubikeyPublicId($command);

        if ($result->didPublicIdMatch()) {
            $procedure->verifySecondFactorIdentifier($result->getPublicId()->getYubikeyPublicId());

            $this->vettingProcedureRepository->store($procedure);
        }

        return $result;
    }

    public function startGssfVerification(string $procedureId): void
    {
        $procedure = $this->getProcedure($procedureId);

        $this->gssfService->startVerification($procedure->getSecondFactor()->secondFactorIdentifier, $procedureId);
    }

    public function verifyGssfId(string $gssfId): GssfVerificationResult
    {
        $result = $this->gssfService->verify($gssfId);

        if (!$result->isSuccess()) {
            return $result;
        }

        $procedure = $this->getProcedure($result->getProcedureId());
        $procedure->verifySecondFactorIdentifier($gssfId);

        $this->vettingProcedureRepository->store($procedure);

        return $result;
    }


    /**
     * @throws UnknownVettingProcedureException
     * @throws DomainException
     */
    public function verifyIdentity(string $procedureId, VerifyIdentityCommand $command): void
    {
        $procedure = $this->getProcedure($procedureId);
        $procedure->verifyIdentity($command->documentNumber, $command->identityVerified);

        $this->vettingProcedureRepository->store($procedure);
    }

    /**
     * @throws UnknownVettingProcedureException
     * @throws DomainException
     */
    public function vet(string $procedureId): ExecutionResult
    {
        $procedure = $this->getProcedure($procedureId);
        $procedure->vet();

        $command = new VetSecondFactorCommand();
        $command->authorityId = $procedure->getAuthorityId();
        $command->identityId = $procedure->getSecondFactor()->identityId;
        $command->secondFactorId = $procedure->getSecondFactor()->id;
        $command->registrationCode = $procedure->getRegistrationCode();
        $command->secondFactorType = $procedure->getSecondFactor()->type;
        $command->secondFactorIdentifier = $procedure->getSecondFactor()->secondFactorIdentifier;
        $command->documentNumber = $procedure->getDocumentNumber();
        $command->identityVerified = $procedure->isIdentityVerified();
        $command->provePossessionSkipped = $procedure->isProvePossessionSkippable();

        $result = $this->commandService->execute($command);

        if ($result->isSuccessful()) {
            $this->vettingProcedureRepository->remove($procedureId);
        }

        return $result;
    }

    /**
     * @throws UnknownVettingProcedureException
     */
    public function getIdentityCommonName(string $procedureId): string
    {
        return $this->getProcedure($procedureId)->getSecondFactor()->commonName;
    }

    /**
     * @throws UnknownVettingProcedureException
     */
    public function isProvePossessionSkippable(string $procedureId): ?bool
    {
        return $this->getProcedure($procedureId)->isProvePossessionSkippable();
    }

    /**
     * @throws UnknownVettingProcedureException
     */
    public function getSecondFactorIdentifier(string $procedureId): string
    {
        return $this->getProcedure($procedureId)->getSecondFactor()->secondFactorIdentifier;
    }


    /**
     * @throws UnknownVettingProcedureException
     */
    public function getSecondFactorId(string $procedureId): string
    {
        return $this->getProcedure($procedureId)->getSecondFactor()->id;
    }

    /**
     * @throws UnknownVettingProcedureException
     */
    private function getProcedure(string $procedureId): ?VettingProcedure
    {
        $procedure = $this->vettingProcedureRepository->retrieve($procedureId);

        if (!$procedure) {
            throw new UnknownVettingProcedureException(
                sprintf("No vetting procedure with id '%s' is known.", $procedureId),
            );
        }

        return $procedure;
    }

    public function hasProcedure(string $procedureId): bool
    {

        return $this->vettingProcedureRepository->retrieve($procedureId) instanceof \Surfnet\StepupRa\RaBundle\VettingProcedure;
    }
}
