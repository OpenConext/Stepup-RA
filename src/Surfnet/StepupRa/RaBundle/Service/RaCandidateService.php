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

use Surfnet\StepupMiddlewareClient\Identity\Dto\RaCandidateSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaCandidateService as ApiRaCandidateService;
use Surfnet\StepupRa\RaBundle\Command\SearchRaCandidatesCommand;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;

class RaCandidateService
{
    /**
     * @var ApiRaCandidateService
     */
    private $apiRaCandidateService;

    public function __construct(ApiRaCandidateService $raCandidateService)
    {
        $this->apiRaCandidateService = $raCandidateService;
    }

    /**
     * @param SearchRaCandidatesCommand $command
     * @return \Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaCandidateCollection
     */
    public function search(SearchRaCandidatesCommand $command)
    {
        $query = new RaCandidateSearchQuery($command->institution, $command->pageNumber);

        if ($command->name) {
            $query->setCommonName($command->name);
        }

        if ($command->email) {
            $query->setEmail($command->email);
        }

        if ($command->orderBy) {
            $query->setOrderBy($command->orderBy);
        }

        if ($command->orderDirection) {
            $query->setOrderDirection($command->orderDirection);
        }

        return $this->apiRaCandidateService->search($query);
    }

    /**
     * @param $identityId
     * @return null|\Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaCandidate
     */
    public function getRaCandidateByIdentityId($identityId)
    {
        if (!is_string($identityId)) {
            throw InvalidArgumentException::invalidType('string', 'identityId', $identityId);
        }

        return $this->apiRaCandidateService->getByIdentityId($identityId);
    }
}
