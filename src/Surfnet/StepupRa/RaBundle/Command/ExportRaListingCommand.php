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

namespace Surfnet\StepupRa\RaBundle\Command;

use DateTime;
use Surfnet\StepupRa\RaBundle\Value\RoleAtInstitution;
use Symfony\Component\Validator\Constraints as Assert;

final class ExportRaListingCommand
{
    #[Assert\NotBlank(message: 'ra.search_ra_listing.actor_id.blank')]
    #[Assert\Type('string', message: 'ra.search_ra_listing.actor_id.type')]
    public $actorId;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $institution;

    /**
     * @var RoleAtInstitution|null
     */
    public $roleAtInstitution;

    /**
     * Builds the command from a SearchRaListingCommand
     */
    public static function fromSearchCommand(SearchRaListingCommand $command): ExportRaListingCommand
    {
        $exportCommand = new self;

        $exportCommand->actorId = $command->actorId;
        $exportCommand->name = $command->name;
        $exportCommand->email = $command->email;
        $exportCommand->institution = $command->institution;
        $exportCommand->roleAtInstitution = $command->roleAtInstitution;

        return $exportCommand;
    }

    public function getFileName(): string
    {
        $date = new DateTime();
        $date = $date->format('Y-m-d');

        $role = $this->roleAtInstitution instanceof RoleAtInstitution && $this->roleAtInstitution->hasRole()
            ? $this->roleAtInstitution->getRole()
            : null;

        return match ($role) {
            'ra' => "ra_export_{$date}",
            'raa' => "raa_export_{$date}",
            default => "ra-raa-export_{$date}",
        };
    }
}
