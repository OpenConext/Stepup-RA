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

use Psr\Log\LoggerInterface;
use Surfnet\StepupMiddlewareClientBundle\Identity\Command\AmendRegistrationAuthorityInformationCommand
    as AmendRegistrationAuthorityInformationApiCommand;
use Surfnet\StepupMiddlewareClientBundle\Identity\Command\AppointRoleCommand;
use Surfnet\StepupMiddlewareClientBundle\Identity\Command\RetractRegistrationAuthorityCommand
    as ApiRetractRegistrationAuthorityCommand;
use Surfnet\StepupRa\RaBundle\Command\AmendRegistrationAuthorityInformationCommand;
use Surfnet\StepupRa\RaBundle\Command\ChangeRaRoleCommand;
use Surfnet\StepupRa\RaBundle\Command\RetractRegistrationAuthorityCommand;

final class RaService
{
    /**
     * @var CommandService
     */
    private $commandService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(CommandService $commandService, LoggerInterface $logger)
    {
        $this->commandService = $commandService;
        $this->logger = $logger;
    }

    public function amendRegistrationAuthorityInformation(AmendRegistrationAuthorityInformationCommand $command): bool
    {
        $apiCommand = new AmendRegistrationAuthorityInformationApiCommand();
        $apiCommand->identityId = $command->identityId;
        $apiCommand->location = $command->location;
        $apiCommand->contactInformation = $command->contactInformation;
        $apiCommand->raInstitution = $command->institution;

        $result = $this->commandService->execute($apiCommand);

        if (!$result->isSuccessful()) {
            $this->logger->error(sprintf(
                "Amending of registration authority %s's information failed: '%s'",
                $apiCommand->identityId,
                implode("', '", $result->getErrors())
            ));
        }

        return $result->isSuccessful();
    }

    public function changeRegistrationAuthorityRole(ChangeRaRoleCommand $command): bool
    {
        $apiCommand             = new AppointRoleCommand();
        $apiCommand->identityId = $command->identityId;
        $apiCommand->role       = $command->role;

        $result = $this->commandService->execute($apiCommand);

        if (!$result->isSuccessful()) {
            $this->logger->error(sprintf(
                'Could not change Identity "%s" role to "%s": "%s"',
                $apiCommand->identityId,
                $apiCommand->role,
                implode("', '", $result->getErrors())
            ));
        }

        return $result->isSuccessful();
    }

    public function retractRegistrationAuthority(RetractRegistrationAuthorityCommand $command): bool
    {
        $apiCommand              = new ApiRetractRegistrationAuthorityCommand();
        $apiCommand->identityId  = $command->identityId;
        $apiCommand->institution = $command->institution;

        $result = $this->commandService->execute($apiCommand);

        if (!$result->isSuccessful()) {
            $this->logger->error(sprintf(
                'Could not retract registration authority for identity "%s": "%s"',
                $apiCommand->identityId,
                implode("', '", $result->getErrors())
            ));
        }

        return $result->isSuccessful();
    }
}
