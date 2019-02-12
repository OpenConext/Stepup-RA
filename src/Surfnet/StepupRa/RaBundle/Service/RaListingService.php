<?php

/**
 * Copyright 2019 SURFnet B.V.
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
use Surfnet\StepupMiddlewareClient\Identity\Dto\RaListingSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaListingService as ApiRaListingService;
use Surfnet\StepupRa\RaBundle\Command\SearchRaListingCommand;

final class RaListingService
{
    /**
     * @var ApiRaListingService
     */
    private $apiRaListingService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ApiRaListingService $raListingService, LoggerInterface $logger)
    {
        $this->apiRaListingService = $raListingService;
        $this->logger = $logger;
    }

    public function search(SearchRaListingCommand $command)
    {
        $query = new RaListingSearchQuery($command->actorId, $command->actorInstitution, $command->pageNumber);

        if ($command->name) {
            $query->setName($command->name);
        }

        if ($command->email) {
            $query->setEmail($command->email);
        }

        if ($command->institution) {
            $query->setInstitution($command->institution);
        }

        if ($command->role) {
            $query->setRole($command->role);
        }

        if ($command->raInstitution) {
            $query->setRaInstitution($command->raInstitution);
        }

        if ($command->orderBy) {
            $query->setOrderBy($command->orderBy);
        }

        if ($command->orderDirection) {
            $query->setOrderDirection($command->orderDirection);
        }

        return $this->apiRaListingService->search($query);
    }
}
