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

use Surfnet\StepupMiddlewareClient\Identity\Dto\SecondFactorAuditLogSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\AuditLog;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\AuditLogService as ApiAuditLogService;
use Surfnet\StepupRa\RaBundle\Command\SearchSecondFactorAuditLogCommand;

class AuditLogService
{
    /**
     * @var ApiAuditLogService
     */
    private $apiAuditLogService;

    public function __construct(ApiAuditLogService $auditLogService)
    {
        $this->apiAuditLogService = $auditLogService;
    }

    /**
     * @param SearchSecondFactorAuditLogCommand $command
     * @return AuditLog
     */
    public function getAuditlog(SearchSecondFactorAuditLogCommand $command): AuditLog
    {
        $query = new SecondFactorAuditLogSearchQuery($command->institution, $command->identityId, $command->pageNumber);
        $query->setOrderBy($command->orderBy);
        $query->setOrderDirection($command->orderDirection);

        $result = $this->apiAuditLogService->searchSecondFactorAuditLog($query);

        if ($result === null) {
            return new AuditLog([], 0, 1, 25);
        }

        return $result;
    }
}
