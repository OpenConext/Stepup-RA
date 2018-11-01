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
use Surfnet\StepupMiddlewareClientBundle\Configuration\Dto\InstitutionConfigurationOptions;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Security\Authorization\Context\InstitutionContext;
use Surfnet\StepupRa\RaBundle\Security\Authorization\Voter\AllowedInOtherInstitutionVoter;
use Surfnet\StepupRa\RaBundle\Service\InstitutionConfigurationOptionsServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

class AllowedInOtherInstitutionVoterTest extends TestCase
{
    /**
     * @test
     * @group security
     * @dataProvider scenarios
     */
    public function test_view_audit_log(
        $expectation,
        array $actorRoles,
        $options,
        $institutionContext,
        $action = null
    )
    {
        $service = m::mock(InstitutionConfigurationOptionsServiceInterface::class);
        $voter = new AllowedInOtherInstitutionVoter($service);

        $token = m::mock(SamlToken::class);

        $token
            ->shouldReceive('getRoles')
            ->once()
            ->andReturn($actorRoles);

        if ($institutionContext instanceof InstitutionContext) {
            $service
                ->shouldReceive('getInstitutionConfigurationOptionsFor')
                ->with($institutionContext->getActorInstitution())
                ->andReturn($options);
        }

        $attributes = [AllowedInOtherInstitutionVoter::VIEW_AUDITLOG];
        if ($action) {
            $attributes = $action;
        }

        $this->assertEquals(
            $expectation,
            $voter->vote($token, $institutionContext, $attributes)
        );
    }

    /**
     * @test
     * @group security
     * @expectedException \InvalidArgumentException
     */
    public function it_considers_the_passing_of_multiple_attributes_as_invalid_input()
    {
        $service = m::mock(InstitutionConfigurationOptionsServiceInterface::class);
        $voter = new AllowedInOtherInstitutionVoter($service);
        $user = m::mock(AbstractToken::class);
        $token = m::mock(SamlToken::class);

        $user
            ->shouldReceive('getRoles')
            ->once()
            ->andReturn(['ROLE_RAA']);

        $token
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($user);

        $voter->vote($token, new InstitutionContext('a', 'b'), [AllowedInOtherInstitutionVoter::VIEW_AUDITLOG, 'arbitrary-attribute']);
    }

    public function scenarios()
    {
        return array_merge($this->granted(), $this->denied(), $this->abstain());
    }

    private function granted()
    {
        return [
            'ra-views-auditlog-of-another-institution' => [
                VoterInterface::ACCESS_GRANTED,
                $this->getRoles(['ROLE_RA']),
                $this->getOptions(['ra' => ['a', 'b'], 'raa' => []]),
                $this->getContext('a', 'b'),
            ],
            'ra-views-auditlog-of-another-institution-extra-role' => [
                VoterInterface::ACCESS_GRANTED,
                $this->getRoles(['ROLE_RA', 'ROLE_USER']),
                $this->getOptions(['ra' => ['a', 'b'], 'raa' => []]),
                $this->getContext('a', 'b'),
            ],
            'raa-views-auditlog-of-another-institution' => [
                VoterInterface::ACCESS_GRANTED,
                $this->getRoles(['ROLE_RAA']),
                $this->getOptions(['ra' => ['a', 'b'], 'raa' => []]),
                $this->getContext('a', 'b'),
            ],
            'raa-views-auditlog-of-another-institution-only-raa' => [
                VoterInterface::ACCESS_GRANTED,
                $this->getRoles(['ROLE_RAA']),
                $this->getOptions(['ra' => [], 'raa' => ['a', 'b']]),
                $this->getContext('a', 'b'),
            ],
            'sraa-views-auditlog-of-another-institution' => [
                VoterInterface::ACCESS_GRANTED,
                $this->getRoles(['ROLE_SRAA']),
                $this->getOptions(['ra' => [], 'raa' => ['a', 'b']]),
                $this->getContext('a', 'b'),
            ],
            'ra-views-auditlog-of-same-institution' => [
                VoterInterface::ACCESS_GRANTED,
                $this->getRoles(['ROLE_RA']),
                $this->getOptions(['ra' => ['a', 'b'], 'raa' => []]),
                $this->getContext('a', 'a'),
            ],
        ];
    }

    private  function denied()
    {
        return [
            'no-ample-institution-config-options-available' => [
                VoterInterface::ACCESS_DENIED,
                $this->getRoles(['ROLE_RAA']),
                $this->getOptions(['ra' => [], 'raa' => []]),
                $this->getContext('a', 'b'),
            ],
            'not-configured-target-institution' => [
                VoterInterface::ACCESS_DENIED,
                $this->getRoles(['ROLE_RAA']),
                $this->getOptions(['ra' => ['a'], 'raa' => []]),
                $this->getContext('a', 'b'),
            ],
            'invalid-role' => [
                VoterInterface::ACCESS_DENIED,
                $this->getRoles(['ROLE_ARA']),
                $this->getOptions(['ra' => [], 'raa' => ['a', 'b']]),
                $this->getContext('a', 'b'),
            ],
        ];
    }

    private  function abstain()
    {
        return [
            'unsupported-attribute' => [
                VoterInterface::ACCESS_ABSTAIN,
                $this->getRoles(['ROLE_RAA']),
                $this->getOptions(['ra' => [], 'raa' => ['a', 'b']]),
                $this->getContext('a', 'b'),
                ['read']
            ],
            'missing-institution-config' => [
                VoterInterface::ACCESS_ABSTAIN,
                $this->getRoles(['ROLE_RA']),
                null,
                $this->getContext('a', 'b'),
            ],
            'invalid-context' => [
                VoterInterface::ACCESS_ABSTAIN,
                $this->getRoles(['ROLE_RA']),
                $this->getOptions(['ra' => [], 'raa' => ['a', 'b']]),
                new \stdClass()
            ],
        ];
    }

    private function getOptions($configuration)
    {
        $options = new InstitutionConfigurationOptions();
        $options->useRa = $configuration['ra'];
        $options->useRaa = $configuration['raa'];
        return $options;
    }

    private function getContext($actorInstitution, $targetInstitution)
    {
        return new InstitutionContext($targetInstitution, $actorInstitution);
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
