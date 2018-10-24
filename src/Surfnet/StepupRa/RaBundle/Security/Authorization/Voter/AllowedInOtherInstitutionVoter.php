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
use Surfnet\StepupRa\RaBundle\Security\Authorization\Context\InstitutionContext;
use Surfnet\StepupRa\RaBundle\Service\InstitutionConfigurationOptionsServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Given a InstitutionContext, votes if allowed to perform actions on
 * a target institution (the institution of the identity we are performing actions on).
 */
class AllowedInOtherInstitutionVoter implements VoterInterface
{
    const VIEW_AUDITLOG = 'view_auditlog';

    private $service;

    public function __construct(InstitutionConfigurationOptionsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) - many simple tests are required to ascertain if the action is
     * allowed
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param InstitutionContext $subject The subject to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        // Check if the class of this object is supported by this voter
        if (!$this->supportsClass(get_class($subject))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // This voter allows one attribute to vote on.
        if (count($attributes) > 1) {
            throw new InvalidArgumentException('Only one attribute is allowed');
        }

        $attribute = $attributes[0];

        // Check if the given attribute is covered by this voter
        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $actorRoles = $token->getRoles();

        // Does the actor have one of the required roles?
        if (!$this->authorizedByRole($actorRoles)) {
            return VoterInterface::ACCESS_DENIED;
        }

        $institutionConfig = $this->service->getInstitutionConfigurationOptionsFor($subject->getActorInstitution());

        if (!$institutionConfig) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $raInstitutions = $institutionConfig->useRa;
        $raaInstitutions = $institutionConfig->useRaa;

        // Now test if any of the roles allow the user to perform the requested task
        foreach ($actorRoles as $role) {
            switch ($role->getRole()) {
                // The SRAA role is always allowed to perform the VIEW_AUDITLOG action
                case "ROLE_SRAA":
                    return VoterInterface::ACCESS_GRANTED;
                    break;
                case "ROLE_RA":
                    // RA roles are allowed if the target institution is in the useRa options.
                    if (in_array($subject->getTargetInstitution(), $raInstitutions)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
                case "ROLE_RAA":
                    // (S)RAA roles are allowed if either the target institution is in the useRa or useRaa options.
                    if (in_array($subject->getTargetInstitution(), array_merge($raInstitutions, $raaInstitutions))) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function supportsAttribute($attribute)
    {
        return in_array($attribute, [self::VIEW_AUDITLOG]);
    }

    private function supportsClass($class)
    {
        $supportedClass = InstitutionContext::class;

        return $supportedClass === $class;
    }

    private function authorizedByRole(array $roles)
    {
        // The role requirements to VIEW_AUDITLOG, one of these roles must be met
        $allowedRoles = ['ROLE_SRAA', 'ROLE_RAA', 'ROLE_RA'];

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
