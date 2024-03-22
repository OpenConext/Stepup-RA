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

namespace Surfnet\StepupRa\RaBundle\Command;

use Symfony\Component\Validator\Constraints as Assert;

class SearchRaCandidatesCommand
{
    #[Assert\NotBlank(message: 'ra.search_ra_candidates.actor_id.blank')]
    public string $actorId;

    public ?string $institution = null;

    public ?string $name = null;

    public ?string $email = null;

    public ?string $raInstitution = null;

    #[Assert\Choice(['name', 'email'], message: 'ra.search_ra_candidates.order_by.invalid_choice')]
    public ?string $orderBy = null;

    #[Assert\Choice(['asc', 'desc'], message: 'ra.search_ra_candidates.order_direction.invalid_choice')]
    public ?string $orderDirection = null;

    #[Assert\GreaterThan(0, message: 'ra.search_ra_candidates.page_number.greater_than_zero')]
    public int $pageNumber;

    public array $institutionFilterOptions;
}
