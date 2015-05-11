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
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Service\IdentityService;
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

    public function __construct(
        IdentityService $identityService,
        AttributeDictionary $attributeDictionary
    ) {
        $this->identityService = $identityService;
        $this->attributeDictionary = $attributeDictionary;
    }

    /**
     * @param SamlToken|TokenInterface $token
     * @return TokenInterface|void
     */
    public function authenticate(TokenInterface $token)
    {
        $translatedAssertion = $this->attributeDictionary->translate($token->assertion);

        $nameId      = $translatedAssertion->getNameID();
        $institution = $translatedAssertion->getAttribute('schacHomeOrganization');

        $identity = $this->identityService->findByNameIdAndInstitution($nameId, $institution);

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
        if ($raCredentials->isSraa) {
            $roles = ['ROLE_SRAA'];
        } elseif ($raCredentials->isRaa) {
            $roles = ['ROLE_RAA'];
        } else {
            $roles = ['ROLE_RA'];
        }

        // set the token
        $authenticatedToken = new SamlToken($token->getLoa(), $roles);
        $authenticatedToken->setUser($identity);

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SamlToken;
    }
}
