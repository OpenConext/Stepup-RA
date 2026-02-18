<?php

/**
 * Copyright 2015 SURFnet bv
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
use Surfnet\StepupMiddlewareClient\Identity\Dto\RaSecondFactorExportQuery;
use Surfnet\StepupMiddlewareClient\Identity\Dto\RaSecondFactorSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Command\RevokeRegistrantsSecondFactorCommand;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaSecondFactorCollection;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaSecondFactorService as ApiRaSecondFactorService;
use Surfnet\StepupRa\RaBundle\Command\ExportRaSecondFactorsCommand;
use Surfnet\StepupRa\RaBundle\Command\RevokeSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaSecondFactorsCommand;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings("PHPMD.NPathComplexity") -- The command to query mapping in search and export exceed the
 * NPathComplexity threshold.
 */
class RaSecondFactorService
{
    public function __construct(
        private readonly ApiRaSecondFactorService $apiRaSecondFactorService,
        private readonly CommandService $commandService,
        private readonly RaSecondFactorExport $exporter,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function revoke(RevokeSecondFactorCommand $command): bool
    {
        $middlewareCommand                 = new RevokeRegistrantsSecondFactorCommand();
        $middlewareCommand->secondFactorId = $command->secondFactorId;
        $middlewareCommand->identityId     = $command->identityId;
        $middlewareCommand->authorityId    = $command->currentUserId;

        $result = $this->commandService->execute($middlewareCommand);

        if (!$result->isSuccessful()) {
            $this->logger->critical(sprintf(
                'Revocation of Second Factor "%s" of Identity "%s" by user "%s" failed: "%s"',
                $middlewareCommand->secondFactorId,
                $middlewareCommand->identityId,
                $middlewareCommand->authorityId,
                implode(", ", $result->getErrors()),
            ));
        }

        return $result->isSuccessful();
    }

    public function search(SearchRaSecondFactorsCommand $command): RaSecondFactorCollection
    {
        $query = new RaSecondFactorSearchQuery($command->pageNumber, $command->actorId);
        $this->buildQuery($command, $query);
        return $this->apiRaSecondFactorService->search($query);
    }

    /**
     * Searches for a collection of second factor tokens and returns a Http response with an attachment
     * Content-Disposition.
     */
    public function export(ExportRaSecondFactorsCommand $command): Response
    {
        $query = new RaSecondFactorExportQuery($command->actorId);
        $this->buildQuery($command, $query);
        $collection = $this->apiRaSecondFactorService->searchForExport($query);
        return $this->exporter->export($collection, $query->getFileName());
    }

    private function buildQuery($command, $query): void
    {
        if ($command->name) {
            $query->setName($command->name);
        }

        if ($command->type) {
            $query->setType($command->type);
        }

        if ($command->secondFactorId) {
            $query->setSecondFactorId($command->secondFactorId);
        }

        if ($command->email) {
            $query->setEmail($command->email);
        }

        if ($command->institution) {
            $query->setInstitution($command->institution);
        }

        if ($command->status) {
            $query->setStatus($command->status);
        }

        if ($command->orderBy) {
            $query->setOrderBy($command->orderBy);
        }

        if ($command->orderDirection) {
            $query->setOrderDirection($command->orderDirection);
        }
    }
}
