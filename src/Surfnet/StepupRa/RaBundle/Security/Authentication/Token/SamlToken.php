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

namespace Surfnet\StepupRa\RaBundle\Security\Authentication\Token;

use SAML2\Assertion;
use Surfnet\StepupBundle\Value\Loa;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SamlToken extends AbstractToken
{
    public Assertion $assertion;

    public function __construct(
        private Loa $loa, array $roles = [])
    {
        parent::__construct($roles);
        $this->setAuthenticated(count($roles));
    }

    /**
     * Returns the user credentials.
     */
    public function getCredentials(): string
    {
        return '';
    }

    public function getLoa(): Loa
    {
        return $this->loa;
    }

    public function serialize()
    {
        return serialize(
            [
                parent::serialize(),
                $this->loa,
            ],
        );
    }

    public function unserialize($serialized)
    {
        [$parent, $this->loa, ] = unserialize(
            $serialized,
        );

        parent::unserialize($parent);
    }
}
