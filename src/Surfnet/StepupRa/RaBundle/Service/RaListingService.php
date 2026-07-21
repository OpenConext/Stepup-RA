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
use Surfnet\StepupRa\RaBundle\Command\ExportRaListingCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaListingCommand;
use Surfnet\StepupRa\RaBundle\Value\RoleAtInstitution;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class RaListingService
{
    public function __construct(
        private ApiRaListingService $apiRaListingService,
        private RaListingExport $raListingExport,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.CyclomaticComplexity") -- The command to query mapping in search exceed the
     * @SuppressWarnings("PHPMD.NPathComplexity") CyclomaticComplexity and NPathComplexity threshold.
     */
    public function search(SearchRaListingCommand $command): RaListingCollection
    {
        $query = $this->buildQuery(
            $command->actorId,
            $command->pageNumber,
            $command->name,
            $command->email,
            $command->institution,
            $command->roleAtInstitution,
            $command->orderBy,
            $command->orderDirection,
        );

        return $this->apiRaListingService->search($query);
    }

    public function get(string $identityId, string $institution, string $actorId): ?RaListing
    {
        return $this->apiRaListingService->get($identityId, $institution, $actorId);
    }

    /**
     * Pages through all RA listing results matching the given filters (bypassing UI pagination)
     * and streams them as a CSV export.
     */
    public function export(ExportRaListingCommand $command): StreamedResponse
    {
        $raListings = [];
        $pageNumber = 1;

        do {
            $query = $this->buildQuery(
                $command->actorId,
                $pageNumber,
                $command->name,
                $command->email,
                $command->institution,
                $command->roleAtInstitution,
            );

            $collection = $this->apiRaListingService->search($query);
            $raListings = array_merge($raListings, $collection->getElements());

            $lastPage = (int) ceil($collection->getTotalItems() / max($collection->getItemsPerPage(), 1));
            $pageNumber++;
        } while ($pageNumber <= $lastPage);

        return $this->raListingExport->export($raListings, $command->getFileName());
    }

    /**
     * @SuppressWarnings("PHPMD.CyclomaticComplexity") -- The command to query mapping exceeds the
     * @SuppressWarnings("PHPMD.NPathComplexity") CyclomaticComplexity and NPathComplexity threshold.
     */
    private function buildQuery(
        string $actorId,
        int $pageNumber,
        ?string $name,
        ?string $email,
        ?string $institution,
        ?RoleAtInstitution $roleAtInstitution,
        ?string $orderBy = null,
        ?string $orderDirection = null,
    ): RaListingSearchQuery {
        $query = new RaListingSearchQuery($actorId, $pageNumber);

        if ($name) {
            $query->setName($name);
        }

        if ($email) {
            $query->setEmail($email);
        }

        if ($institution) {
            $query->setInstitution($institution);
        }

        if ($roleAtInstitution && $roleAtInstitution->hasRole()) {
            $query->setRole($roleAtInstitution->getRole());
        }

        if ($roleAtInstitution && $roleAtInstitution->hasInstitution()) {
            $query->setRaInstitution($roleAtInstitution->getInstitution());
        }

        if ($orderBy) {
            $query->setOrderBy($orderBy);
        }

        if ($orderDirection) {
            $query->setOrderDirection($orderDirection);
        }

        return $query;
    }
}
