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

namespace Surfnet\StepupRa\SamlStepupProviderBundle\Saml;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

final class StateHandler
{
    public function __construct(private readonly AttributeBag $attributeBag, private readonly string $provider)
    {
    }

    public function setRequestId(string $originalRequestId): StateHandler
    {
        $this->set('request_id', $originalRequestId);

        return $this;
    }

    public function getRequestId(): ?string
    {
        return $this->get('request_id');
    }

    public function clear(): void
    {
        $this->attributeBag->remove($this->provider);
    }

    protected function set($key, $value): void
    {
        $this->attributeBag->set($this->provider . '/' . $key, $value);
    }

    protected function get($key)
    {
        return $this->attributeBag->get($this->provider . '/' . $key);
    }
}
