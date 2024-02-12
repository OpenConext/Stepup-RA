<?php

/**
 * Copyright 2016 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Tests\Security\Authentication\Token;

use PHPUnit\Framework\TestCase;
use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupMiddlewareClientBundle\Configuration\Dto\InstitutionConfigurationOptions;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;

class SamlTokenTest extends TestCase
{
    /**
     * @test
     * @group saml
     * @group security
     * @group serialization
     */
    public function saml_token_is_correctly_serialized_and_unserialized()
    {
        $institutionConfigurationOptions = new InstitutionConfigurationOptions();
        $institutionConfigurationOptions->useRaLocations = true;
        $institutionConfigurationOptions->showRaaContactInformation = false;

        $samlToken = new SamlToken(
            new Loa(Loa::LOA_1, 'http://some.url.tld/authentication/loa1'),
            ['ROLE_RAA'],
        );

        $serialized = $samlToken->__serialize();

        $deserialized = new SamlToken(new Loa(Loa::LOA_2, 'http://some.url.tld/authentication/loa2'));
        $deserialized->__unserialize($serialized);

        $this->assertEquals($samlToken, $deserialized);
    }
}
