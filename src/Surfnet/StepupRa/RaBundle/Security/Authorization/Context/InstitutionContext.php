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

namespace Surfnet\StepupRa\RaBundle\Security\Authorization\Context;

/**
 * Authorization context for FGA institution related authorization checks
 * This context is set with the institution of the target (the identity
 * that is operated on) and the actor (the logged in user with role:
 * (S)RA(A)).
 *
 * The context is used in the RaInOtherInstitutionVoter, and can be used
 * from the context of a controller or service that uses the AuthzChecker
 *
 * Example:
 * $context = new InstitutionContext('institution-a', 'institution-d');
 * $securityChecker->isGranted('view_auditlog', $context);
 *
 * @see RaInOtherInstitutionVoter
 */
final class InstitutionContext
{
    private $targetInstitution;

    private $actorInstitution;

    /**
     * @param string $targetInstitution
     * @param string $actorInstitution
     */
    public function __construct($targetInstitution, $actorInstitution)
    {
        $this->targetInstitution = $targetInstitution;
        $this->actorInstitution = $actorInstitution;
    }

    /**
     * @return string
     */
    public function getTargetInstitution()
    {
        return $this->targetInstitution;
    }

    /**
     * @return string
     */
    public function getActorInstitution()
    {
        return $this->actorInstitution;
    }
}
