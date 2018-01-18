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
use Surfnet\StepupMiddlewareClientBundle\Configuration\Dto\InstitutionConfigurationOptions;
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

    /**
     * @var InstitutionConfigurationOptions
     */
    private $institutionConfigurationOptions;

    public function __construct(
        Loa $loa,
        array $roles = [],
        InstitutionConfigurationOptions $institutionConfigurationOptions = null
    ) {
        parent::__construct($roles);

        $this->loa = $loa;
        $this->setAuthenticated(count($roles));
        $this->institutionConfigurationOptions = $institutionConfigurationOptions;
    }

    /**
     * @return InstitutionConfigurationOptions
     */
    public function getInstitutionConfigurationOptions()
    {
        return $this->institutionConfigurationOptions;
    }

    /**
     * @param string $institution
     * @param InstitutionConfigurationOptions $institutionConfigurationOptions
     */
    public function changeInstitutionScope(
        $institution,
        InstitutionConfigurationOptions $institutionConfigurationOptions
    ) {
        if ($this->getUser() === null) {
            throw new LogicException('Cannot change institution scope: token does not contain a user');
        }

        $roles = array_map(function (RoleInterface $role) {
            return $role->getRole();
        }, $this->getRoles());

        if (!in_array('ROLE_SRAA', $roles)) {
            throw new RuntimeException(sprintf(
                'Unauthorized to change institution scope to "%s": role SRAA required, found roles "%s"',
                $institution,
                implode(', ', $roles)
            ));
        }

        $this->getUser()->institution = $institution;
        $this->institutionConfigurationOptions = $institutionConfigurationOptions;
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
    public function getLoa()
    {
        return $this->loa;
    }

    public function serialize()
    {
        return serialize([parent::serialize(), $this->loa, $this->institutionConfigurationOptions]);
    }

    public function unserialize($serialized)
    {
        list($parent, $this->loa, $this->institutionConfigurationOptions) = unserialize($serialized);

        parent::unserialize($parent);
    }
}
