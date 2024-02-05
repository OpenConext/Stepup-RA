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
use Surfnet\StepupMiddlewareClient\Configuration\Dto\RaLocationSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Configuration\Command\AddRaLocationCommand as MiddlewareCreateLocationCommand;
use Surfnet\StepupMiddlewareClientBundle\Configuration\Command\ChangeRaLocationCommand as MiddlewareChangeRaLocationCommand;
use Surfnet\StepupMiddlewareClientBundle\Configuration\Command\RemoveRaLocationCommand as MiddlewareRemoveRaLocationCommand;
use Surfnet\StepupMiddlewareClientBundle\Configuration\Dto\RaLocation;
use Surfnet\StepupMiddlewareClientBundle\Configuration\Dto\RaLocationCollection;
use Surfnet\StepupMiddlewareClientBundle\Configuration\Service\RaLocationService as ApiRaLocationService;
use Surfnet\StepupMiddlewareClientBundle\Uuid\Uuid;
use Surfnet\StepupRa\RaBundle\Command\ChangeRaLocationCommand;
use Surfnet\StepupRa\RaBundle\Command\CreateRaLocationCommand;
use Surfnet\StepupRa\RaBundle\Command\RemoveRaLocationCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaLocationsCommand;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RaLocationService
{
    public function __construct(
        private readonly ApiRaLocationService $apiRaLocationService,
        private readonly CommandService $commandService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function find(string $id): ?RaLocation
    {
        return $this->apiRaLocationService->get($id);
    }

    public function search(SearchRaLocationsCommand $command): RaLocationCollection
    {
        $query = new RaLocationSearchQuery($command->institution);

        if ($command->orderBy) {
            $query->setOrderBy($command->orderBy);
        }

        if ($command->orderDirection) {
            $query->setOrderDirection($command->orderDirection);
        }

        return $this->apiRaLocationService->search($query);
    }

    public function create(CreateRaLocationCommand $command): bool
    {
        $middlewareCommand = new MiddlewareCreateLocationCommand();
        $middlewareCommand->id = Uuid::generate();
        $middlewareCommand->name = $command->name;
        $middlewareCommand->institution = $command->institution;
        $middlewareCommand->contactInformation = $command->contactInformation;
        $middlewareCommand->location = $command->location;

        $result = $this->commandService->execute($middlewareCommand);

        if (!$result->isSuccessful()) {
            $this->logger->critical(sprintf(
                'Creation of RA location "%s" for institution "%s" by user "%s" failed: "%s"',
                $middlewareCommand->name,
                $middlewareCommand->institution,
                $command->currentUserId,
                implode(", ", $result->getErrors()),
            ));
        }

        return $result->isSuccessful();
    }

    public function change(ChangeRaLocationCommand $command): bool
    {
        $middlewareCommand = new MiddlewareChangeRaLocationCommand();
        $middlewareCommand->id = $command->id;
        $middlewareCommand->name = $command->name;
        $middlewareCommand->institution = $command->institution;
        $middlewareCommand->contactInformation = $command->contactInformation;
        $middlewareCommand->location = $command->location;

        $result = $this->commandService->execute($middlewareCommand);

        if (!$result->isSuccessful()) {
            $this->logger->critical(sprintf(
                'Changing of RA location "%s" for institution "%s" by user "%s" failed: "%s"',
                $middlewareCommand->name,
                $middlewareCommand->institution,
                $command->currentUserId,
                implode(", ", $result->getErrors()),
            ));
        }

        return $result->isSuccessful();
    }

    public function remove(RemoveRaLocationCommand $command): bool
    {
        $middlewareCommand = new MiddlewareRemoveRaLocationCommand();
        $middlewareCommand->institution = $command->institution;
        $middlewareCommand->raLocationId = $command->locationId;
        $result = $this->commandService->execute($middlewareCommand);

        if (!$result->isSuccessful()) {
            $this->logger->critical(sprintf(
                'Removal of RA location "%s" of institution "%s" by user "%s" failed: "%s"',
                $middlewareCommand->raLocationId,
                $middlewareCommand->institution,
                $command->currentUserId,
                implode(", ", $result->getErrors()),
            ));
        }

        return $result->isSuccessful();
    }
}
