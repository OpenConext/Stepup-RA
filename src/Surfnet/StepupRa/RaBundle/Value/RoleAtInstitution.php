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

use Surfnet\StepupRa\RaBundle\Assert;

class RoleAtInstitution
{
    /**
     * @var string|null
     */
    private $role = null;

    /**
     * @var string|null
     */
    private $institution = null;

    /**
     * @param string $role
     */
    public function setRole($role): void
    {
        Assert::nullOrString($role, 'Role must be null or a string value');
        $this->role = $role;
    }

    /**
     * @param string $institution
     */
    public function setInstitution($institution): void
    {
        Assert::nullOrString($institution, 'Institution must be null or a string value');
        $this->institution = $institution;
    }

    /**
     * @return bool
     */
    public function hasRole(): bool
    {
        return !is_null($this->role);
    }

    /**
     * @return bool
     */
    public function hasInstitution(): bool
    {
        return !is_null($this->institution);
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return string
     */
    public function getInstitution(): string
    {
        return $this->institution;
    }
}
