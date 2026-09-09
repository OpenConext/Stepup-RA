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

namespace Surfnet\StepupRa\RaBundle\Tests\Command;

use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Surfnet\StepupRa\RaBundle\Command\ExportRaListingCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaListingCommand;
use Surfnet\StepupRa\RaBundle\Value\RoleAtInstitution;

class ExportRaListingCommandTest extends TestCase
{
    #[Test]
    public function from_search_command_copies_the_relevant_filter_fields()
    {
        $roleAtInstitution = new RoleAtInstitution();
        $roleAtInstitution->setRole('ra');

        $searchCommand = new SearchRaListingCommand();
        $searchCommand->actorId = 'actor-id';
        $searchCommand->name = 'Jane Doe';
        $searchCommand->email = 'jane@example.org';
        $searchCommand->institution = 'institution-a';
        $searchCommand->roleAtInstitution = $roleAtInstitution;
        $searchCommand->pageNumber = 3;
        $searchCommand->orderBy = 'name';
        $searchCommand->orderDirection = 'desc';

        $exportCommand = ExportRaListingCommand::fromSearchCommand($searchCommand);

        $this->assertSame('actor-id', $exportCommand->actorId);
        $this->assertSame('Jane Doe', $exportCommand->name);
        $this->assertSame('jane@example.org', $exportCommand->email);
        $this->assertSame('institution-a', $exportCommand->institution);
        $this->assertSame($roleAtInstitution, $exportCommand->roleAtInstitution);
        $this->assertSame('name', $exportCommand->orderBy);
        $this->assertSame('desc', $exportCommand->orderDirection);
    }

    #[Test]
    #[DataProvider('fileNameProvider')]
    public function get_file_name_reflects_the_role_filter(?string $role, string $expectedPrefix)
    {
        $command = new ExportRaListingCommand();
        $command->actorId = 'actor-id';

        if ($role !== null) {
            $roleAtInstitution = new RoleAtInstitution();
            $roleAtInstitution->setRole($role);
            $command->roleAtInstitution = $roleAtInstitution;
        }

        $date = (new DateTime())->format('Y-m-d');

        $this->assertSame($expectedPrefix . '_' . $date, $command->getFileName());
    }

    public static function fileNameProvider(): array
    {
        return [
            'ra role filter' => ['ra', 'ra_export'],
            'raa role filter' => ['raa', 'raa_export'],
            'no role filter' => [null, 'ra-raa-export'],
        ];
    }

    #[Test]
    public function get_file_name_treats_role_at_institution_without_a_role_as_unfiltered()
    {
        $command = new ExportRaListingCommand();
        $command->actorId = 'actor-id';
        $command->roleAtInstitution = new RoleAtInstitution();

        $date = (new DateTime())->format('Y-m-d');

        $this->assertSame('ra-raa-export_' . $date, $command->getFileName());
    }
}
