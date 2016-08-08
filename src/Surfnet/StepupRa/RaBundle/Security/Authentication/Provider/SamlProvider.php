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

namespace Surfnet\StepupRa\RaBundle\Security\Authentication\Provider;

use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\StepupRa\RaBundle\Exception\InconsistentStateException;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Service\IdentityService;
use Surfnet\StepupRa\RaBundle\Service\InstitutionConfigurationOptionsService;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class SamlProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\IdentityService
     */
    private $identityService;

    /**
     * @var \Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary
     */
    private $attributeDictionary;

    /**
     * @var InstitutionConfigurationOptionsService
     */
    private $institutionConfigurationOptionsService;

    public function __construct(
        IdentityService $identityService,
        AttributeDictionary $attributeDictionary,
        InstitutionConfigurationOptionsService $institutionConfigurationOptionsService
    ) {
        $this->identityService                        = $identityService;
        $this->attributeDictionary                    = $attributeDictionary;
        $this->institutionConfigurationOptionsService = $institutionConfigurationOptionsService;
    }

    /**
     * @param SamlToken|TokenInterface $token
     * @return TokenInterface|void
     */
    public function authenticate(TokenInterface $token)
    {
        $translatedAssertion = $this->attributeDictionary->translate($token->assertion);

        $nameId       = $translatedAssertion->getNameID();
        $institutions = $translatedAssertion->getAttributeValue('schacHomeOrganization');

        if (empty($institutions)) {
            throw new BadCredentialsException(
                'No schacHomeOrganization provided'
            );
        }

        if (count($institutions) > 1) {
            throw new BadCredentialsException(
                'Multiple schacHomeOrganizations provided'
            );
        }

        $identity = $this->identityService->findByNameIdAndInstitution($nameId, $institutions[0]);

        // if no identity can be found, we're done.
        if ($identity === null) {
            throw new BadCredentialsException(
                'Unable to find Identity matching the criteria. Has the identity been registered before?'
            );
        }

        $raCredentials = $this->identityService->getRaCredentials($identity);

        // if no credentials can be found, we're done.
        if (!$raCredentials) {
            throw new BadCredentialsException(
                'The Identity is not registered as (S)RA(A) and therefor does not have access to this application'
            );
        }

        // determine the role based on the credentials given
        $roles = [];
        if ($raCredentials->isSraa) {
            $roles[] = 'ROLE_SRAA';
        }

        if ($raCredentials->isRaa) {
            $roles[] = 'ROLE_RAA';
        } else {
            $roles[] = 'ROLE_RA';
        }

        $institutionConfigurationOptions = $this->institutionConfigurationOptionsService
            ->getInstitutionConfigurationOptionsFor($identity->institution);

        if ($institutionConfigurationOptions === null) {
            throw new InconsistentStateException(
                sprintf(
                    'InstitutionConfigurationOptions for institution "%s" '
                    . 'must exist but cannot be found after authenticating Identity "%s"',
                    $identity->institution,
                    $identity->id
                )
            );
        }

        // set the token
        $authenticatedToken = new SamlToken($token->getLoa(), $roles, $institutionConfigurationOptions);
        $authenticatedToken->setUser($identity);

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SamlToken;
    }
}
