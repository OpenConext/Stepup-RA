<?php

/**
 * Copyright 2026 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Tests\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Surfnet\StepupMiddlewareClient\Identity\Dto\SecondFactorAuditLogSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\AuditLog;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\AuditLogService as ApiAuditLogService;
use Surfnet\StepupRa\RaBundle\Command\SearchSecondFactorAuditLogCommand;
use Surfnet\StepupRa\RaBundle\Service\AuditLogService;

class AuditLogServiceTest extends TestCase
{
    #[Test]
    public function the_actor_id_is_sent_to_the_middleware()
    {
        $auditLog = new AuditLog([], 0, 1, 25);

        $apiService = $this->createMock(ApiAuditLogService::class);
        $apiService
            ->expects($this->once())
            ->method('searchSecondFactorAuditLog')
            ->with($this->callback(
                fn (SecondFactorAuditLogSearchQuery $query) => str_contains($query->toHttpQuery(), 'actorId=actor-id'),
            ))
            ->willReturn($auditLog);

        $service = new AuditLogService($apiService);

        $command = new SearchSecondFactorAuditLogCommand();
        $command->actorId = 'actor-id';
        $command->institution = 'institution-a.example.com';
        $command->identityId = 'identity-id';
        $command->pageNumber = 1;
        $command->orderBy = 'recordedOn';
        $command->orderDirection = 'desc';

        $this->assertSame($auditLog, $service->getAuditlog($command));
    }
}
