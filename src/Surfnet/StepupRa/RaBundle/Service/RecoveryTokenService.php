<?php

/**
 * Copyright 2022 SURFnet bv
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

use Surfnet\StepupMiddlewareClient\Identity\Dto\RecoveryTokenSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RecoveryTokenCollection;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\RecoveryTokenService as ApiRecoveryTokenService;
use Surfnet\StepupRa\RaBundle\Command\RevokeSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRecoveryTokensCommand;

class RecoveryTokenService
{
    private $commandService;

    /**
     * @var ApiRecoveryTokenService
     */
    private $apiRecoveryTokenService;

    public function __construct(CommandService $commandService, ApiRecoveryTokenService $apiRecoveryTokenService)
    {
        $this->commandService = $commandService;
        $this->apiRecoveryTokenService = $apiRecoveryTokenService;
    }

    public function revoke(RevokeSecondFactorCommand $command)
    {
        // Todo
    }

    public function search(SearchRecoveryTokensCommand $command): RecoveryTokenCollection
    {
        $query = new RecoveryTokenSearchQuery($command->pageNumber, $command->actorId);

        if ($command->name) {
            $query->setName($command->name);
        }

        if ($command->type) {
            $query->setType($command->type);
        }

        if ($command->status) {
            $query->setStatus($command->status);
        }

        if ($command->email) {
            $query->setEmail($command->email);
        }

        if ($command->institution) {
            $query->setInstitution($command->institution);
        }

        if ($command->orderBy) {
            $query->setOrderBy($command->orderBy);
        }

        if ($command->orderDirection) {
            $query->setOrderDirection($command->orderDirection);
        }

        return $this->apiRecoveryTokenService->search($query);
    }
}
