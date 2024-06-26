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

namespace Surfnet\StepupRa\SamlStepupProviderBundle\Provider;

use Surfnet\SamlBundle\Entity\IdentityProvider;
use Surfnet\SamlBundle\Entity\ServiceProvider;
use Surfnet\StepupRa\SamlStepupProviderBundle\Saml\StateHandler;

final class Provider
{
    /**
     * @param string           $name
     */
    public function __construct(
        private $name,
        private readonly ServiceProvider $serviceProvider,
        private readonly IdentityProvider $remoteIdentityProvider,
        private readonly StateHandler $stateHandler,
    ) {
    }

    public function getStateHandler(): StateHandler
    {
        return $this->stateHandler;
    }

    public function getRemoteIdentityProvider(): IdentityProvider
    {
        return $this->remoteIdentityProvider;
    }

    public function getServiceProvider(): ServiceProvider
    {
        return $this->serviceProvider;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
