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

namespace Surfnet\StepupRa\RaBundle\Tests\Security\Authorization\Voter;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListingCollection;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Security\Authorization\Voter\AllowedToSwitchInstitutionVoter;
use Surfnet\StepupRa\RaBundle\Service\RaListingService;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

class AllowedToSwitchInstitutionVoterTest extends TestCase
{
    /**
     * @test
     * @group security
     * @dataProvider scenarios
     */
    public function test_view_audit_log(
        $expectation,
        array $actorRoles,
        $raListingCount,
        $attributes
    )
    {
        $user = new Identity();
        $user->id = '47820bdd-7e48-46e5-916b-0b38b65f4d6b';

        $token = m::mock(SamlToken::class);
        $token
            ->shouldReceive('getRoles')
            ->once()
            ->andReturn($actorRoles);

        $token
            ->shouldReceive('getUser')
            ->andReturn($user);

        $token
            ->shouldReceive('getSchacHomeInstitution')
            ->once()
            ->andReturn('institution-a.example.com');

        $collection = m::mock(RaListingCollection::class);
        $collection
            ->shouldReceive('getTotalItems')
            ->andReturn($raListingCount);

        $service = m::mock(RaListingService::class);
        $service
            ->shouldReceive('searchBy')
            ->andReturn($collection);

        $voter = new AllowedToSwitchInstitutionVoter($service);

        $this->assertEquals(
            $expectation,
            $voter->vote($token, null, $attributes)
        );
    }

    public function scenarios()
    {
        return [
            'raa-with-two-institutions' => [
                VoterInterface::ACCESS_GRANTED,
                $this->getRoles(['ROLE_RAA']),
                2,
                [AllowedToSwitchInstitutionVoter::RAA_SWITCHING]
            ],
            'raa-with-multiple-institutions' => [
                VoterInterface::ACCESS_GRANTED,
                $this->getRoles(['ROLE_RAA']),
                8,
                [AllowedToSwitchInstitutionVoter::RAA_SWITCHING]
            ],
            'sraa-with-multiple-institutions' => [
                VoterInterface::ACCESS_GRANTED,
                $this->getRoles(['ROLE_SRAA']),
                8,
                [AllowedToSwitchInstitutionVoter::RAA_SWITCHING]
            ],
            'raa-with-one-institution' => [
                VoterInterface::ACCESS_DENIED,
                $this->getRoles(['ROLE_RAA']),
                1,
                [AllowedToSwitchInstitutionVoter::RAA_SWITCHING]
            ],
            'sraa-with-one-institution' => [
                VoterInterface::ACCESS_DENIED,
                $this->getRoles(['ROLE_SRAA']),
                1,
                [AllowedToSwitchInstitutionVoter::RAA_SWITCHING]
            ],
            'raa-with-invalid-attributes' => [
                VoterInterface::ACCESS_ABSTAIN,
                $this->getRoles(['ROLE_RAA']),
                3,
                ['sraa_switcher']
            ],
            'sraa-with-invalid-attributes' => [
                VoterInterface::ACCESS_ABSTAIN,
                $this->getRoles(['ROLE_SRAA']),
                3,
                ['sraa_switcher']
            ],
        ];
    }


    private function getRoles(array $rawRoles)
    {
        $roles = [];
        foreach ($rawRoles as $role){
            $roles[] = new Role($role);
        }
        return $roles;
    }
}
