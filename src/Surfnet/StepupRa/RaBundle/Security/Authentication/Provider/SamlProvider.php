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

namespace Surfnet\StepupRa\RaBundle\Security\Authentication\Provider;

use BadMethodCallException;
use Psr\Log\LoggerInterface;
use SAML2\Assertion;
use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\SamlBundle\SAML2\Response\AssertionAdapter;
use Surfnet\SamlBundle\Security\Authentication\Provider\SamlProviderInterface;
use Surfnet\StepupBundle\Service\LoaResolutionService;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Exception\MissingRequiredAttributeException;
use Surfnet\StepupRa\RaBundle\Exception\UserNotRaException;
use Surfnet\StepupRa\RaBundle\Security\AuthenticatedIdentity;
use Surfnet\StepupRa\RaBundle\Service\IdentityService;
use Surfnet\StepupRa\RaBundle\Service\ProfileService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects") - The SamlProvider needs to test several authorizations in order
 *  to determine the user may, or may not log in. This causes the coupling.
 */
class SamlProvider implements SamlProviderInterface, UserProviderInterface
{
    public function __construct(
        #[Autowire(service: 'ra.service.identity')]
        private readonly IdentityService $identityService,
        private readonly ProfileService $profileService,
        private readonly AttributeDictionary $attributeDictionary,
        private readonly LoggerInterface $logger,
        private readonly LoaResolutionService $loaResolutionService,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.NPathComplexity") - The authorization tests cause the complexity to raise, could and
     * @SuppressWarnings("PHPMD.CyclomaticComplexity") might be changed by introducing additional utility classes.
     */
    public function getUser(Assertion $assertion): UserInterface
    {
        $translatedAssertion = $this->attributeDictionary->translate($assertion);

        $nameId = $translatedAssertion->getNameID();
        $institution = $this->getSingleStringValue('schacHomeOrganization', $translatedAssertion);
        $identity = $this->identityService->findByNameIdAndInstitution($nameId, $institution);

        // if no identity can be found, we're done.
        if (!$identity instanceof Identity) {
            throw new BadCredentialsException(
                'Unable to find Identity matching the criteria. Has the identity been registered before?',
            );
        }

        $profile = $this->profileService->findByIdentityId($identity->id);

        // if no credentials can be found, we're done.
        if (!$profile->isSraa && empty($profile->authorizations) && empty($profile->management)) {
            throw new UserNotRaException(
                'The Identity is not registered as (S)RA(A) and therefore does not have access to this application',
            );
        }

        // determine the role based on the credentials given
        $roles = [];
        if ($profile->isSraa) {
            $roles[] = 'ROLE_SRAA';
        }

        // Get authorizations (explicit RA(A) roles use_ra/use_raa).
        foreach ($profile->authorizations as $role) {
            if ($role[0] == 'raa' && !in_array('ROLE_RAA', $roles)) {
                $roles[] = 'ROLE_RAA';
            }
            if ($role[0] == 'ra' && !in_array('ROLE_RA', $roles)) {
                $roles[] = 'ROLE_RA';
            }
        }

        $loa = $this->loaResolutionService->getLoa($assertion->getAuthnContextClassRef());

        return new AuthenticatedIdentity($identity, $loa, $roles);
    }

    private function getSingleStringValue(string $attributeName, AssertionAdapter $translatedAssertion): string
    {
        $values = $translatedAssertion->getAttributeValue($attributeName);

        if (empty($values)) {
            throw new MissingRequiredAttributeException(
                sprintf(
                    'Missing a required SAML attribute. This application requires the "%s" attribute to function.',
                    $attributeName,
                ),
            );
        }

        // see https://www.pivotaltracker.com/story/show/121296389
        if (count($values) > 1) {
            $this->logger->warning(sprintf(
                'Found "%d" values for attribute "%s", using first value',
                count($values),
                $attributeName,
            ));
        }

        $value = reset($values);

        if (!is_string($value)) {
            $message = sprintf(
                'First value of attribute "%s" must be a string, "%s" given',
                $attributeName,
                get_debug_type($value),
            );

            $this->logger->warning($message);

            throw new MissingRequiredAttributeException($message);
        }

        return $value;
    }

    public function getNameId(Assertion $assertion): string
    {
        return $this->attributeDictionary->translate($assertion)->getNameID();
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === AuthenticatedIdentity::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new BadMethodCallException('Use `getUser` to load a user by username');
    }
}
