<?php

/**
 * Copyright 2015 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Surfnet\StepupRa\RaBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    #[\PHPUnit\Framework\Attributes\Group('configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_second_factors_to_be_configured()
    {
        $configuration = [
            'session_lifetimes'      => [
                'max_absolute_lifetime' => 3600,
                'max_relative_lifetime' => 600
            ]
        ];

        $this->assertConfigurationIsInvalid([$configuration], 'must be configured');
    }

    #[\PHPUnit\Framework\Attributes\Group('configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_session_timeout_configuration()
    {
        $configuration = ['enabled_second_factors' => ['sms']];

        $this->assertConfigurationIsInvalid([$configuration], 'must be configured');
    }

    #[\PHPUnit\Framework\Attributes\Group('configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_maximum_absolute_timeout_to_be_configured()
    {
        $configuration = [
            'enabled_second_factors' => ['sms'],
            'session_lifetimes' => ['max_relative_lifetime' => 600]
        ];

        $this->assertConfigurationIsInvalid([$configuration], 'must be configured');
    }

    #[\PHPUnit\Framework\Attributes\Group('configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_maximum_relative_timeout_to_be_configured()
    {
        $configuration = [
            'enabled_second_factors' => ['sms'],
            'session_lifetimes' => ['max_absolute_lifetime' => 3600]
        ];

        $this->assertConfigurationIsInvalid([$configuration], 'must be configured');
    }

    #[\PHPUnit\Framework\Attributes\Group('configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_allows_one_enabled_second_factor()
    {
        $this->assertConfigurationIsValid([['enabled_second_factors' => ['sms']]], 'enabled_second_factors');
    }

    #[\PHPUnit\Framework\Attributes\Group('configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_allows_two_enabled_second_factors()
    {
        $this->assertConfigurationIsValid([['enabled_second_factors' => ['sms', 'yubikey']]], 'enabled_second_factors');
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}
