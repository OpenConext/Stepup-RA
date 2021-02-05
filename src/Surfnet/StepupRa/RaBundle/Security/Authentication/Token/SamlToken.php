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

use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupRa\RaBundle\Exception\LogicException;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Role\RoleInterface;

class SamlToken extends AbstractToken
{
    /**
     * @var \SAML2\Assertion
     */
    public $assertion;

    /**
     * @var \Surfnet\StepupBundle\Value\Loa
     */
    private $loa;

    public function __construct(Loa $loa, array $roles = [])
    {
        parent::__construct($roles);

        $this->loa = $loa;
        $this->setAuthenticated(!empty($roles));
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return '';
    }

    /**
     * @return Loa
     */
    public function getLoa(): Loa
    {
        return $this->loa;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(
            [
                parent::serialize(),
                $this->loa,
            ]
        );
    }

    /**
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        list(
            $parent,
            $this->loa,
            ) = unserialize(
                $serialized
            );

        parent::unserialize($parent);
    }
}
