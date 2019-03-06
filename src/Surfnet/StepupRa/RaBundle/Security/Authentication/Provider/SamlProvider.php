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

use Psr\Log\LoggerInterface;
use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\SamlBundle\SAML2\Response\AssertionAdapter;
use Surfnet\StepupRa\RaBundle\Exception\MissingRequiredAttributeException;
use Surfnet\StepupRa\RaBundle\Exception\UserNotRaException;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Service\IdentityService;
use Surfnet\StepupRa\RaBundle\Service\profileService;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) - The SamlProvider needs to test several authorizations in order
 *  to determine the user may, or may not log in. This causes the coupling.
 */
class SamlProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\IdentityService
     */
    private $identityService;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\profileService
     */
    private $profileService;

    /**
     * @var \Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary
     */
    private $attributeDictionary;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityService $identityService,
        ProfileService $profileService,
        AttributeDictionary $attributeDictionary,
        LoggerInterface $logger
    ) {
        $this->identityService                        = $identityService;
        $this->profileService                        = $profileService;
        $this->attributeDictionary                    = $attributeDictionary;
        $this->logger                                 = $logger;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)      - The authorization tests cause the complexity to raise, could and
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)   might be changed by introducing additional utility classes.
     *                                                 Consider rebuilding this in the future.
     * @param SamlToken|TokenInterface $token
     * @return TokenInterface|void
     */
    public function authenticate(TokenInterface $token)
    {
        $translatedAssertion = $this->attributeDictionary->translate($token->assertion);

        $nameId   = $translatedAssertion->getNameID();
        $institution = $this->getSingleStringValue('schacHomeOrganization', $translatedAssertion);
        $identity = $this->identityService->findByNameIdAndInstitution($nameId, $institution);

        // if no identity can be found, we're done.
        if ($identity === null) {
            throw new BadCredentialsException(
                'Unable to find Identity matching the criteria. Has the identity been registered before?'
            );
        }

        $profile = $this->profileService->findByIdentityId($identity->id);

        // if no credentials can be found, we're done.
        if (empty($profile->authorizations)) {
            throw new UserNotRaException(
                'The Identity is not registered as (S)RA(A) and therefor does not have access to this application'
            );
        }

        // determine the role based on the credentials given
        $roles = [];
        if ($profile->isSraa) {
            $roles[] = 'ROLE_SRAA';
        }

        foreach ($profile->authorizations as $institution => $role) {
            if ($role[0] == 'raa' && !in_array('ROLE_RAA', $roles)) {
                $roles[] = 'ROLE_RAA';
            }
            if ($role[0] == 'ra' && !in_array('ROLE_RA', $roles)) {
                $roles[] = 'ROLE_RA';
            }
        }

        // set the token
        $authenticatedToken = new SamlToken($token->getLoa(), $roles);
        $authenticatedToken->setUser($identity);

        return $authenticatedToken;
    }

    private function getSingleStringValue($attribute, AssertionAdapter $translatedAssertion)
    {
        $values = $translatedAssertion->getAttributeValue($attribute);

        if (empty($values)) {
            throw new MissingRequiredAttributeException(
                sprintf(
                    'Missing a required SAML attribute. This application requires the "%s" attribute to function.',
                    $attribute
                )
            );
        }

        // see https://www.pivotaltracker.com/story/show/121296389
        if (count($values) > 1) {
            $this->logger->warning(sprintf(
                'Found "%d" values for attribute "%s", using first value',
                count($values),
                $attribute
            ));
        }

        $value = reset($values);

        if (!is_string($value)) {
            $message = sprintf(
                'First value of attribute "%s" must be a string, "%s" given',
                $attribute,
                is_object($value) ? get_class($value) : gettype($value)
            );

            $this->logger->warning($message);

            throw new MissingRequiredAttributeException($message);
        }

        return $value;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SamlToken;
    }
}
