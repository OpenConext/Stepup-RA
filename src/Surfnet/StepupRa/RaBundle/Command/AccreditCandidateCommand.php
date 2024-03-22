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

use Surfnet\StepupRa\RaBundle\Value\RoleAtInstitution;
use Symfony\Component\Validator\Constraints as Assert;

class AccreditCandidateCommand
{
    public string $identityId;

    public string $institution;

    #[Assert\NotBlank(message: 'ra.accredit_candidate.location.may_not_be_blank')]
    public string $location;

    #[Assert\NotBlank(message: 'ra.accredit_candidate.contact_information.may_not_be_blank')]
    public string $contactInformation;

    public RoleAtInstitution $roleAtInstitution;

    public array $availableInstitutions = [];
}
