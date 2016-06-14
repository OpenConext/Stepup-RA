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

use Surfnet\StepupBundle\Command\SendSmsChallengeCommand;
use Surfnet\StepupBundle\Command\VerifyPossessionOfPhoneCommand;
use Surfnet\StepupBundle\Service\SmsSecondFactor\OtpVerification;
use Surfnet\StepupBundle\Service\SmsSecondFactorService;
use Surfnet\StepupBundle\Value\PhoneNumber\InternationalPhoneNumber;
use Surfnet\StepupBundle\Value\SecondFactorType;
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
    /**
     * @var \Surfnet\StepupBundle\Service\SmsSecondFactorService
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

    public function __construct(
        SmsSecondFactorService $smsSecondFactorService,
        YubikeySecondFactorService $yubikeySecondFactorService,
        GssfService $gssfService,
        U2fService $u2fService,
        CommandService $commandService,
        VettingProcedureRepository $vettingProcedureRepository,
        TranslatorInterface $translator,
        IdentityService $identityService
    ) {
        $this->smsSecondFactorService = $smsSecondFactorService;
        $this->yubikeySecondFactorService = $yubikeySecondFactorService;
        $this->gssfService = $gssfService;
        $this->u2fService = $u2fService;
        $this->commandService = $commandService;
        $this->vettingProcedureRepository = $vettingProcedureRepository;
        $this->translator = $translator;
        $this->identityService = $identityService;
    }

    /**
     * @param StartVettingProcedureCommand $command
     * @return bool
     */
    public function isLoaSufficientToStartProcedure(StartVettingProcedureCommand $command)
    {
        $secondFactorType = new SecondFactorType($command->secondFactor->type);

        return $secondFactorType->isSatisfiedBy($command->authorityLoa);
    }

    /**
     * @param StartVettingProcedureCommand $command
     * @return string The procedure ID.
     */
    public function startProcedure(StartVettingProcedureCommand $command)
    {
        $this->smsSecondFactorService->clearSmsVerificationState();

        if (!$this->isLoaSufficientToStartProcedure($command)) {
            throw new LoaTooLowException(
                sprintf(
                    "Registration authority has LoA '%s', which is not enough to allow vetting of a '%s' second factor",
                    $command->authorityLoa,
                    $command->secondFactor->type
                )
            );
        }

        $procedure = VettingProcedure::start(
            $command->secondFactor->id,
            $command->authorityId,
            $command->registrationCode,
            $command->secondFactor
        );

        $this->vettingProcedureRepository->store($procedure);

        return $procedure->getId();
    }

    /**
     * @param string $procedureId
     * @throws UnknownVettingProcedureException
     */
    public function cancelProcedure($procedureId)
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
    public function getSmsOtpRequestsRemainingCount()
    {
        return $this->smsSecondFactorService->getOtpRequestsRemainingCount();
    }

    /**
     * @return int
     */
    public function getSmsMaximumOtpRequestsCount()
    {
        return $this->smsSecondFactorService->getMaximumOtpRequestsCount();
    }

    /**
     * @param string $procedureId
     * @param SendSmsChallengeCommand $command
     * @return bool
     * @throws UnknownVettingProcedureException
     * @throws DomainException
     */
    public function sendSmsChallenge($procedureId, SendSmsChallengeCommand $command)
    {
        $procedure = $this->getProcedure($procedureId);

        $phoneNumber = InternationalPhoneNumber::fromStringFormat(
            $procedure->getSecondFactor()->secondFactorIdentifier
        );

        $identity = $this->identityService->findById($procedure->getSecondFactor()->identityId);

        if (!$identity) {
            throw new DomainException("Second factor is coupled to an identity that doesn't exist");
        }

        $command->phoneNumber = $phoneNumber;
        $command->body        = $this->translator->trans('ra.vetting.sms.challenge_body', [], 'messages', $identity->preferredLocale);
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
    public function verifyPhoneNumber($procedureId, VerifyPossessionOfPhoneCommand $command)
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
     * @return YubikeySecondFactor\VerificationResult
     */
    public function verifyYubikeyPublicId($procedureId, VerifyYubikeyPublicIdCommand $command)
    {
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
    public function startGssfVerification($procedureId)
    {
        $procedure = $this->getProcedure($procedureId);

        $this->gssfService->startVerification($procedure->getSecondFactor()->secondFactorIdentifier, $procedureId);
    }

    /**
     * @param string $gssfId
     * @return GssfVerificationResult
     */
    public function verifyGssfId($gssfId)
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
    public function createU2fSignRequest($procedureId)
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
    public function verifyU2fAuthentication($procedureId, SignRequest $signRequest, SignResponse $signResponse)
    {
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
    public function verifyIdentity($procedureId, VerifyIdentityCommand $command)
    {
        $procedure = $this->getProcedure($procedureId);
        $procedure->verifyIdentity($command->documentNumber, $command->identityVerified);

        $this->vettingProcedureRepository->store($procedure);
    }

    /**
     * @param string $procedureId
     * @return bool
     * @throws UnknownVettingProcedureException
     * @throws DomainException
     */
    public function vet($procedureId)
    {
        $procedure = $this->getProcedure($procedureId);
        $procedure->vet();

        $command = new VetSecondFactorCommand();
        $command->authorityId = $procedure->getAuthorityId();
        $command->identityId = $procedure->getSecondFactor()->identityId;
        $command->secondFactorId = $procedure->getSecondFactor()->id;
        $command->registrationCode = $procedure->getRegistrationCode();
        $command->secondFactorType = $procedure->getSecondFactor()->type;
        $command->secondFactorIdentifier = $procedure->getInputSecondFactorIdentifier();
        $command->documentNumber = $procedure->getDocumentNumber();
        $command->identityVerified = $procedure->isIdentityVerified();

        $result = $this->commandService->execute($command);

        if (!$result->isSuccessful()) {
            return false;
        }

        $this->vettingProcedureRepository->remove($procedureId);

        return true;
    }

    /**
     * @param string $procedureId
     * @return string
     * @throws UnknownVettingProcedureException
     */
    public function getIdentityCommonName($procedureId)
    {
        return $this->getProcedure($procedureId)->getSecondFactor()->commonName;
    }

    /**
     * @param $procedureId
     * @return string
     * @throws UnknownVettingProcedureException
     */
    public function getSecondFactorIdentifier($procedureId)
    {
        return $this->getProcedure($procedureId)->getSecondFactor()->secondFactorIdentifier;
    }

    /**
     * @param string $procedureId
     * @return null|VettingProcedure
     * @throws UnknownVettingProcedureException
     */
    private function getProcedure($procedureId)
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
    public function hasProcedure($procedureId)
    {
        if (!is_string($procedureId)) {
            throw InvalidArgumentException::invalidType('string', 'procedureId', $procedureId);
        }

        return $this->vettingProcedureRepository->retrieve($procedureId) !== null;
    }
}
