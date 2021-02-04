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

use DateInterval;
use RuntimeException;
use Surfnet\StepupBundle\Command\SendSmsChallengeCommand;
use Surfnet\StepupBundle\Command\VerifyPossessionOfPhoneCommand;
use Surfnet\StepupBundle\Service\SecondFactorTypeService;
use Surfnet\StepupBundle\Service\SmsSecondFactor\OtpVerification;
use Surfnet\StepupBundle\Service\SmsSecondFactorServiceInterface;
use Surfnet\StepupBundle\Value\PhoneNumber\InternationalPhoneNumber;
use Surfnet\StepupBundle\Value\SecondFactorType;
use Surfnet\StepupMiddlewareClient\Service\ExecutionResult;
use Surfnet\StepupMiddlewareClient\Service\VerificationResult;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\SecondFactorService;
use Surfnet\StepupMiddlewareClientBundle\Identity\Command\VetSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\Command\CreateU2fSignRequestCommand;
use Surfnet\StepupRa\RaBundle\Command\StartVettingProcedureCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyIdentityCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyU2fAuthenticationCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyPublicIdCommand;
use Surfnet\StepupRa\RaBundle\Exception\DomainException;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;
use Surfnet\StepupRa\RaBundle\Exception\LoaTooLowException;
use Surfnet\StepupRa\RaBundle\Exception\UnknownVettingProcedureException;
use Surfnet\StepupRa\RaBundle\Repository\VettingProcedureRepository;
use Surfnet\StepupRa\RaBundle\Service\Gssf\VerificationResult as GssfVerificationResult;
use Surfnet\StepupRa\RaBundle\Service\U2f\AuthenticationVerificationResult;
use Surfnet\StepupRa\RaBundle\Service\U2f\SignRequestCreationResult;
use Surfnet\StepupRa\RaBundle\Service\YubikeySecondFactor\VerificationResult as YubikeyVerificationResult;
use Surfnet\StepupRa\RaBundle\Value\DateTime;
use Surfnet\StepupRa\RaBundle\VettingProcedure;
use Surfnet\StepupU2fBundle\Dto\SignRequest;
use Surfnet\StepupU2fBundle\Dto\SignResponse;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class VettingService
{
    const REGISTRATION_CODE_EXPIRED_ERROR =
        'Surfnet\Stepup\Exception\DomainException: Cannot vet second factor, the registration window is closed.';

    /**
     * @var \Surfnet\StepupBundle\Service\SmsSecondFactorServiceInterface
     */
    private $smsSecondFactorService;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\YubikeySecondFactorService
     */
    private $yubikeySecondFactorService;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\GssfService
     */
    private $gssfService;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\U2fService
     */
    private $u2fService;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\CommandService
     */
    private $commandService;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Repository\VettingProcedureRepository
     */
    private $vettingProcedureRepository;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\IdentityService
     */
    private $identityService;

    /**
     * @var \Surfnet\StepupBundle\Service\SecondFactorTypeService
     */
    private $secondFactorTypeService;

    /**
     * @var SecondFactorService
     */
    private $secondFactorService;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        SmsSecondFactorServiceInterface $smsSecondFactorService,
        YubikeySecondFactorService $yubikeySecondFactorService,
        GssfService $gssfService,
        U2fService $u2fService,
        CommandService $commandService,
        VettingProcedureRepository $vettingProcedureRepository,
        TranslatorInterface $translator,
        IdentityService $identityService,
        SecondFactorTypeService $secondFactorTypeService,
        SecondFactorService $secondFactorService
    ) {
        $this->smsSecondFactorService = $smsSecondFactorService;
        $this->yubikeySecondFactorService = $yubikeySecondFactorService;
        $this->gssfService = $gssfService;
        $this->u2fService = $u2fService;
        $this->commandService = $commandService;
        $this->vettingProcedureRepository = $vettingProcedureRepository;
        $this->translator = $translator;
        $this->identityService = $identityService;
        $this->secondFactorTypeService = $secondFactorTypeService;
        $this->secondFactorService = $secondFactorService;
    }

    /**
     * @param StartVettingProcedureCommand $command
     * @return bool
     */
    public function isLoaSufficientToStartProcedure(StartVettingProcedureCommand $command): bool
    {
        $secondFactorType = new SecondFactorType($command->secondFactor->type);

        return $this->secondFactorTypeService->isSatisfiedBy($secondFactorType, $command->authorityLoa);
    }

    /**
     * @param StartVettingProcedureCommand $command
     * @return bool
     */
    public function isExpiredRegistrationCode(StartVettingProcedureCommand $command): bool
    {
        return DateTime::now()->comesAfter(
            new DateTime(
                $command->secondFactor->registrationRequestedAt
                    ->add(new DateInterval('P14D'))
                    ->setTime(23, 59, 59)
            )
        );
    }

    /**
     * @param StartVettingProcedureCommand $command
     * @return string The procedure ID.
     */
    public function startProcedure(StartVettingProcedureCommand $command): string
    {
        $this->smsSecondFactorService->clearSmsVerificationState();

        if (!$this->isLoaSufficientToStartProcedure($command)) {
            throw new LoaTooLowException(
                sprintf(
                    "Registration authority has LoA '%u', which is not enough to allow vetting of a '%s' second factor",
                    (string) $command->authorityLoa,
                    $command->secondFactor->type
                )
            );
        }

        $provePossessionSkipped = $this->secondFactorService
            ->getVerifiedCanSkipProvePossession($command->secondFactor->id);

        $procedure = VettingProcedure::start(
            $command->secondFactor->id,
            $command->authorityId,
            $command->registrationCode,
            $command->secondFactor,
            $provePossessionSkipped
        );

        $this->vettingProcedureRepository->store($procedure);

        return $procedure->getId();
    }

    /**
     * @param string $procedureId
     * @throws UnknownVettingProcedureException
     */
    public function cancelProcedure(string $procedureId): void
    {
        if (!is_string($procedureId)) {
            throw InvalidArgumentException::invalidType('string', 'procedureId', $procedureId);
        }

        $procedure = $this->vettingProcedureRepository->retrieve($procedureId);

        if (!$procedure) {
            throw new UnknownVettingProcedureException(
                sprintf("No vetting procedure with id '%s' is known.", $procedureId)
            );
        }

        $this->vettingProcedureRepository->remove($procedureId);
    }

    /**
     * @return int
     */
    public function getSmsOtpRequestsRemainingCount(): int
    {
        return $this->smsSecondFactorService->getOtpRequestsRemainingCount();
    }

    /**
     * @return int
     */
    public function getSmsMaximumOtpRequestsCount(): int
    {
        return $this->smsSecondFactorService->getMaximumOtpRequestsCount();
    }

    /**
     * @param string $procedureId
     * @param SendSmsChallengeCommand $command
     * @return bool
     * @throws UnknownVettingProcedureException
     * @throws RuntimeException
     */
    public function sendSmsChallenge(string $procedureId, SendSmsChallengeCommand $command): bool
    {
        $procedure = $this->getProcedure($procedureId);

        $phoneNumber = InternationalPhoneNumber::fromStringFormat(
            $procedure->getSecondFactor()->secondFactorIdentifier
        );

        $identity = $this->identityService->findById($procedure->getSecondFactor()->identityId);

        if (!$identity) {
            throw new RuntimeException("Second factor is coupled to an identity that doesn't exist");
        }

        $command->phoneNumber = $phoneNumber;
        $command->body        = $this->translator->trans(
            'ra.vetting.sms.challenge_body',
            [],
            'messages',
            $identity->preferredLocale
        );
        $command->identity    = $procedure->getSecondFactor()->identityId;
        $command->institution = $procedure->getSecondFactor()->institution;

        return $this->smsSecondFactorService->sendChallenge($command);
    }

    /**
     * @param string                   $procedureId
     * @param VerifyPossessionOfPhoneCommand $command
     * @return OtpVerification
     * @throws UnknownVettingProcedureException
     * @throws DomainException
     */
    public function verifyPhoneNumber(string $procedureId, VerifyPossessionOfPhoneCommand $command): OtpVerification
    {
        $procedure = $this->getProcedure($procedureId);

        $verification = $this->smsSecondFactorService->verifyPossession($command);

        if (!$verification->wasSuccessful()) {
            return $verification;
        }

        $procedure->verifySecondFactorIdentifier($verification->getPhoneNumber());
        $this->vettingProcedureRepository->store($procedure);

        return $verification;
    }

    /**
     * @param string                       $procedureId
     * @param VerifyYubikeyPublicIdCommand $command
     * @return YubikeyVerificationResult
     */
    public function verifyYubikeyPublicId(
        string $procedureId,
        VerifyYubikeyPublicIdCommand $command
    ): YubikeyVerificationResult {
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

    /**
     * @param string $procedureId
     */
    public function startGssfVerification(string $procedureId): string
    {
        $procedure = $this->getProcedure($procedureId);

        $this->gssfService->startVerification($procedure->getSecondFactor()->secondFactorIdentifier, $procedureId);
    }

    /**
     * @param string $gssfId
     * @return GssfVerificationResult
     */
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
     * @param string $procedureId
     * @return SignRequestCreationResult
     */
    public function createU2fSignRequest(string $procedureId): SignRequestCreationResult
    {
        $procedure = $this->getProcedure($procedureId);

        $command = new CreateU2fSignRequestCommand();
        $command->keyHandle = $procedure->getSecondFactor()->secondFactorIdentifier;
        $command->identityId = $procedure->getSecondFactor()->identityId;
        $command->institution = $procedure->getSecondFactor()->institution;

        return $this->u2fService->createSignRequest($command);
    }

    /**
     * @param string       $procedureId
     * @param SignRequest  $signRequest
     * @param SignResponse $signResponse
     * @return AuthenticationVerificationResult
     */
    public function verifyU2fAuthentication(
        string $procedureId,
        SignRequest $signRequest,
        SignResponse $signResponse
    ): AuthenticationVerificationResult {
        $procedure = $this->getProcedure($procedureId);

        $command = new VerifyU2fAuthenticationCommand();
        $command->identityId = $procedure->getSecondFactor()->identityId;
        $command->institution = $procedure->getSecondFactor()->institution;
        $command->signRequest = $signRequest;
        $command->signResponse = $signResponse;

        $result = $this->u2fService->verifyAuthentication($command);

        if ($result->wasSuccessful()) {
            $procedure->verifySecondFactorIdentifier($signResponse->keyHandle);
            $this->vettingProcedureRepository->store($procedure);
        }

        return $result;
    }

    /**
     * @param string $procedureId
     * @param VerifyIdentityCommand $command
     * @return void
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
     * @param string $procedureId
     * @return \Surfnet\StepupMiddlewareClient\Service\ExecutionResult
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
     * @param string $procedureId
     * @return string
     * @throws UnknownVettingProcedureException
     */
    public function getIdentityCommonName(string $procedureId): string
    {
        return $this->getProcedure($procedureId)->getSecondFactor()->commonName;
    }

    /**
     * @param $procedureId
     * @return bool|null
     * @throws UnknownVettingProcedureException
     */
    public function isProvePossessionSkippable(string $procedureId): ?bool
    {
        return $this->getProcedure($procedureId)->isProvePossessionSkippable();
    }

    /**
     * @param $procedureId
     * @return string
     * @throws UnknownVettingProcedureException
     */
    public function getSecondFactorIdentifier(string $procedureId): string
    {
        return $this->getProcedure($procedureId)->getSecondFactor()->secondFactorIdentifier;
    }

    /**
     * @param string $procedureId
     * @return null|VettingProcedure
     * @throws UnknownVettingProcedureException
     */
    private function getProcedure(string $procedureId): ?VettingProcedure
    {
        if (!is_string($procedureId)) {
            throw InvalidArgumentException::invalidType('string', 'procedureId', $procedureId);
        }

        $procedure = $this->vettingProcedureRepository->retrieve($procedureId);

        if (!$procedure) {
            throw new UnknownVettingProcedureException(
                sprintf("No vetting procedure with id '%s' is known.", $procedureId)
            );
        }

        return $procedure;
    }

    /**
     * @param string $procedureId
     * @return bool
     */
    public function hasProcedure(string $procedureId): bool
    {
        if (!is_string($procedureId)) {
            throw InvalidArgumentException::invalidType('string', 'procedureId', $procedureId);
        }

        return $this->vettingProcedureRepository->retrieve($procedureId) !== null;
    }
}
