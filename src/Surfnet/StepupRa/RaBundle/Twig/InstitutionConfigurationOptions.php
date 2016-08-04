<?php

/**
 * Copyright 2016 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Twig;

use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class InstitutionConfigurationOptions
{
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return boolean
     */
    public function useRaLocations()
    {
        /** @var SamlToken $token */
        $token = $this->tokenStorage->getToken();

        return $token->getInstitutionConfigurationOptions()->useRaLocations;
    }

    /**
     * @return boolean
     */
    public function showRaaContactInformation()
    {
        /** @var SamlToken $token */
        $token = $this->tokenStorage->getToken();

        return $token->getInstitutionConfigurationOptions()->showRaaContactInformation;
    }
}
