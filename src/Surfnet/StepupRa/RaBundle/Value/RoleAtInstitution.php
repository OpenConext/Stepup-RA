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

namespace Surfnet\StepupRa\RaBundle\Value;

class RoleAtInstitution
{
    private ?string $role = null;

    private ?string $institution = null;

    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    public function setInstitution(?string $institution): void
    {
        $this->institution = $institution;
    }

    public function hasRole(): bool
    {
        return !is_null($this->role);
    }

    public function hasInstitution(): bool
    {
        return !is_null($this->institution);
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getInstitution(): ?string
    {
        return $this->institution;
    }
}
