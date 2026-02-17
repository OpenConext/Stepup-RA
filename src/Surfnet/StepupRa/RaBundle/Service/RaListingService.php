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

use Surfnet\StepupMiddlewareClient\Identity\Dto\RaListingSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListing;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListingCollection;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaListingService as ApiRaListingService;
use Surfnet\StepupRa\RaBundle\Command\SearchRaListingCommand;

final readonly class RaListingService
{
    public function __construct(
        private ApiRaListingService $apiRaListingService,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.CyclomaticComplexity") -- The command to query mapping in search exceed the
     * @SuppressWarnings("PHPMD.NPathComplexity") CyclomaticComplexity and NPathComplexity threshold.
     */
    public function search(SearchRaListingCommand $command): RaListingCollection
    {
        $query = new RaListingSearchQuery($command->actorId, $command->pageNumber);

        if ($command->name) {
            $query->setName($command->name);
        }

        if ($command->email) {
            $query->setEmail($command->email);
        }

        if ($command->institution) {
            $query->setInstitution($command->institution);
        }

        if ($command->roleAtInstitution && $command->roleAtInstitution->hasRole()) {
            $query->setRole($command->roleAtInstitution->getRole());
        }

        if ($command->roleAtInstitution && $command->roleAtInstitution->hasInstitution()) {
            $query->setRaInstitution($command->roleAtInstitution->getInstitution());
        }

        if ($command->orderBy) {
            $query->setOrderBy($command->orderBy);
        }

        if ($command->orderDirection) {
            $query->setOrderDirection($command->orderDirection);
        }

        return $this->apiRaListingService->search($query);
    }

    public function get(string $identityId, string $institution, string $actorId): ?RaListing
    {
        return $this->apiRaListingService->get($identityId, $institution, $actorId);
    }
}
