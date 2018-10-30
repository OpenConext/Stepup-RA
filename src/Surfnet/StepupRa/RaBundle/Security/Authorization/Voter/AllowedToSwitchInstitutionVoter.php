<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Security\Authorization\Voter;

use InvalidArgumentException;
use Surfnet\StepupMiddlewareClient\Identity\Dto\RaListingSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaListingService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Votes whether or not a RAA user is allowed to see the institution switcher
 *
 * The ROLE_RAA is allowed to switch institutions when (s)he is:
 *  - is RAA
 *  - for more than one institution
 */
class AllowedToSwitchInstitutionVoter implements VoterInterface
{
    const RAA_SWITCHING = 'raa_switching';

    /**
     * @var RaListingService
     */
    private $service;

    public function __construct(RaListingService $service)
    {
        $this->service = $service;
    }

    /**
     * @param TokenInterface $token A TokenInterface instance
     * @param $subject not used
     * @param array $attributes contains the subject (triggered from twig is_granted function)
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        // Check if the class of this object is supported by this voter
        if (!$this->supportsAttribute(reset($attributes))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // This voter allows one attribute to vote on.
        if (count($attributes) > 1) {
            throw new InvalidArgumentException('Only one attribute is allowed');
        }

        $actorRoles = $token->getRoles();

        // Does the actor have one of the required roles?
        if (!$this->authorizedByRole($actorRoles)) {
            return VoterInterface::ACCESS_DENIED;
        }

        $query = new RaListingSearchQuery($token->getIdentityInstitution(), 1);
        $query->setIdentityId($token->getIdentityInstitution());
        $raListing = $this->service->search($query);

        if ($raListing->getTotalItems() > 1) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function supportsAttribute($attribute)
    {
        return in_array($attribute, [self::RAA_SWITCHING]);
    }

    private function authorizedByRole(array $roles)
    {
        $allowedRoles = ['ROLE_SRAA', 'ROLE_RAA'];

        // Convert the Role[] to an array of strings representing the role names.
        $roles = array_map(
            function (Role $role) {
                return $role->getRole();
            },
            $roles
        );

        // And test if there is an intersection (is one or more of the token roles also in the allowed roles)
        return count(array_intersect($roles, $allowedRoles)) > 0;
    }
}
