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

namespace Surfnet\StepupRa\RaBundle\Command;

use Symfony\Component\Validator\Constraints as Assert;

final class SearchRecoveryTokensCommand
{
    #[Assert\NotBlank(message: 'ra.search_ra_recovery_tokens.actor.blank')]
    public string $actorId = '';

    public ?string $name = null;

    public ?string $type = null;

    public string $status = '';

    public ?string $email = null;

    #[Assert\Choice(['name', 'type', 'email', 'status'], message: 'ra.search_ra_recovery_tokens.order_by.invalid_choice')]
    public ?string $orderBy = null;

    #[Assert\Choice(['asc', 'desc'], message: 'ra.search_ra_recovery_tokens.order_direction.invalid_choice')]
    public ?string $orderDirection = null;

    #[Assert\GreaterThan(0, message: 'ra.search_ra_recovery_tokens.page_number.greater_than_zero')]
    public int $pageNumber = 0;

    /**
     * The institution to filter the results on
     */
    public ?string $institution = null;

    public array $institutionFilterOptions = [];
}
