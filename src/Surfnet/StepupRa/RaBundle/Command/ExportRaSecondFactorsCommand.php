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

namespace Surfnet\StepupRa\RaBundle\Command;

use Symfony\Component\Validator\Constraints as Assert;

final class ExportRaSecondFactorsCommand
{
    public const STATUS_UNVERIFIED = 'unverified';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_VETTED = 'vetted';
    public const STATUS_REVOKED = 'revoked';

    /**
     *
     * @var string
     */
    #[Assert\NotBlank(message: 'ra.search_ra_second_factors.actor.blank')]
    #[Assert\Type('string', message: 'ra.search_ra_second_factors.actor.type')]
    public $actorId;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var string|null The second factor type's ID (eg. Yubikey public ID)
     */
    public $secondFactorId;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $institution;

    /**
     * @var string|null One of the STATUS_* constants.
     */
    #[Assert\Choice(['unverified', 'verified', 'vetted', 'revoked'], message: 'ra.search_ra_second_factors.status.invalid_choice')]
    public $status;

    /**
     * @var string|null
     */
    #[Assert\Choice(['name', 'type', 'secondFactorId', 'email', 'status'], message: 'ra.search_ra_second_factors.order_by.invalid_choice')]
    public $orderBy;

    /**
     * @var string|null
     */
    #[Assert\Choice(['asc', 'desc'], message: 'ra.search_ra_second_factors.order_direction.invalid_choice')]
    public $orderDirection;

    /**
     * Builds the command from a SearchRaSecondFactorsCommand
     */
    public static function fromSearchCommand(SearchRaSecondFactorsCommand $command): ExportRaSecondFactorsCommand
    {
        $exportCommand = new self;

        $exportCommand->actorId = $command->actorId;
        $exportCommand->name = $command->name;
        $exportCommand->type = $command->type;
        $exportCommand->secondFactorId = $command->secondFactorId;
        $exportCommand->email = $command->email;
        $exportCommand->institution = $command->institution;
        $exportCommand->status = $command->status;
        $exportCommand->orderBy = $command->orderBy;
        $exportCommand->orderDirection = $command->orderDirection;

        return $exportCommand;
    }
}
