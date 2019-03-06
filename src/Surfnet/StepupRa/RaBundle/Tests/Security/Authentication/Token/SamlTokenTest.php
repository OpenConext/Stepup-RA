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

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupMiddlewareClientBundle\Configuration\Dto\InstitutionConfigurationOptions;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Exception\LogicException;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
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
            $institutionConfigurationOptions
        );

        $serialized = $samlToken->serialize();

        $deserialized = new SamlToken(new Loa(Loa::LOA_2, 'http://some.url.tld/authentication/loa2'));
        $deserialized->unserialize($serialized);

        $this->assertEquals($samlToken, $deserialized);
    }

    /**
     * @test
     * @group authorization
     * @group security
     * @group sraa
     */
    public function institution_scope_of_saml_token_cannot_be_changed_when_not_sraa()
    {
        $this->setExpectedException(RuntimeException::class, 'Unauthorized to change institution scope');

        $identity = new Identity();

        $samlToken = new SamlToken(
            new Loa(Loa::LOA_1, 'http://some.url.tld/authentication/loa1'),
            ['ROLE_RAA', 'ROLE_RA']
        );
        $samlToken->setUser($identity);

        $samlToken->changeInstitutionScope('surfnet.nl');
    }

    /**
     * @test
     * @group authorization
     * @group security
     * @group sraa
     */
    public function institution_scope_of_saml_token_can_be_changed_when_sraa()
    {
        $expectedInstitution = 'surfnet.nl';

        $oldInstitutionConfigurationOptions                            = new InstitutionConfigurationOptions();
        $oldInstitutionConfigurationOptions->useRaLocations            = true;
        $oldInstitutionConfigurationOptions->showRaaContactInformation = true;

        $identity = new Identity();
        $identity->institution = 'old-institution.nl';

        $samlToken = new SamlToken(
            new Loa(Loa::LOA_1, 'http://some.url.tld/authentication/loa1'),
            ['ROLE_SRAA'],
            $oldInstitutionConfigurationOptions
        );
        $samlToken->setUser($identity);

        $samlToken->changeInstitutionScope($expectedInstitution);

        $this->assertSame($expectedInstitution, $samlToken->getUser()->institution);
    }

    /**
     * @test
     * @group authorization
     * @group security
     */
    public function institution_scope_of_saml_token_cannot_be_changed_if_it_has_no_user()
    {
        $this->setExpectedException(LogicException::class, 'token does not contain a user');

        $samlToken = new SamlToken(
            new Loa(Loa::LOA_1, 'http://some.url.tld/authentication/loa1'),
            ['ROLE_SRAA']
        );

        $samlToken->changeInstitutionScope('surfnet.nl');
    }
}
