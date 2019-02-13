<?php

/**
 * Copyright 2019 SURFnet B.V.
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

class SearchRaListingCommand
{
    /**
     * @Assert\NotBlank(message="ra.search_ra_candidates.actor_id.blank")
     * @Assert\Type("string", message="ra.search_ra_candidates.actor_id.type")
     *
     * @var string
     */
    public $actorId;

    /**
     * @Assert\NotBlank(message="ra.search_ra_candidates.actor_institution.blank")
     * @Assert\Type("string", message="ra.search_ra_candidates.actor_institution.type")
     *
     * @var string
     */
    public $actorInstitution;

    /**
     * @var string
     */
    public $institution;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string
     */
    public $role;

    /**
     * @var string
     */
    public $raInstitution;

    /**
     * @Assert\Choice(
     *     {"name", "email"},
     *     message="ra.search_ra_candidates.order_by.invalid_choice"
     * )
     *
     * @var string|null
     */
    public $orderBy;

    /**
     * @Assert\Choice({"asc", "desc"}, message="ra.search_ra_candidates.order_direction.invalid_choice")
     *
     * @var string|null
     */
    public $orderDirection;

    /**
     * @Assert\Type("integer", message="ra.search_ra_candidates.page_number.type")
     * @Assert\GreaterThan(0, message="ra.search_ra_candidates.page_number.greater_than_zero")
     *
     * @var int
     */
    public $pageNumber;

    /**
     * @var array
     */
    public $institutionFilterOptions;

    /**
     * @var array
     */
    public $raInstitutionFilterOptions;
}
