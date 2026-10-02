<?php

/**
 * Copyright 2026 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Tests\Controller;

use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Profile;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\VettingTypeHint;
use Surfnet\StepupRa\Kernel;
use Surfnet\StepupRa\RaBundle\Security\AuthenticatedIdentity;
use Surfnet\StepupRa\RaBundle\Service\ProfileService;
use Surfnet\StepupRa\RaBundle\Service\VettingTypeHintService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class VettingTypeHintControllerFunctionalTest extends WebTestCase
{
    private const HOME_INSTITUTION = 'home.example.org';
    private const OTHER_INSTITUTION = 'other.example.org';

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function test_selecting_a_non_home_institution_and_saving_hints_keeps_them_in_sync(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $identity = Identity::fromData([
            'id' => 'identity-1',
            'name_id' => 'urn:collab:person:home.example.org:raa',
            'institution' => self::HOME_INSTITUTION,
            'email' => 'raa@example.org',
            'common_name' => 'Test RAA',
            'preferred_locale' => 'en_GB',
        ]);
        $authenticatedIdentity = new AuthenticatedIdentity($identity, new Loa(Loa::LOA_2, 'loa2'), ['ROLE_RAA']);

        $profile = Profile::fromData([
            'id' => $identity->id,
            'name_id' => $identity->nameId,
            'institution' => self::HOME_INSTITUTION,
            'email' => $identity->email,
            'common_name' => $identity->commonName,
            'preferred_locale' => $identity->preferredLocale,
            'authorizations' => [
                self::HOME_INSTITUTION => ['raa'],
                self::OTHER_INSTITUTION => ['raa'],
            ],
            'is_sraa' => false,
        ]);

        $profileService = $this->createMock(ProfileService::class);
        $profileService->method('findByIdentityId')->willReturn($profile);
        self::getContainer()->set(ProfileService::class, $profileService);

        $vettingTypeHintService = $this->createMock(VettingTypeHintService::class);
        $vettingTypeHintService->method('findBy')->willReturnCallback(
            static fn(string $institution): ?VettingTypeHint => null,
        );
        $vettingTypeHintService->expects($this->once())
            ->method('save')
            ->with($this->callback(
                static fn($command): bool => $command->institution === self::OTHER_INSTITUTION,
            ))
            ->willReturn(true);
        self::getContainer()->set(VettingTypeHintService::class, $vettingTypeHintService);

        self::getContainer()->get('request_stack')->push(
            Request::create('https://ra.dev.openconext.local/', server: ['HTTPS' => 'on']),
        );
        $client->loginUser($authenticatedIdentity, 'saml_based');

        $client->request(Request::METHOD_GET, '/vetting-type-hint', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::HOME_INSTITUTION, (string) $client->getResponse()->getContent());

        $crawler = $client->getCrawler();
        $selectForm = $crawler->filter('#select_institution_select_and_apply')->form();
        $selectForm['select_institution[institution]'] = self::OTHER_INSTITUTION;
        $client->submit($selectForm, [], ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::OTHER_INSTITUTION, (string) $client->getResponse()->getContent());

        $crawler = $client->getCrawler();
        $saveForm = $crawler->filter('#vetting_type_hint_continue')->form();
        $saveForm['vetting_type_hint[vetting_type_hint_en_GB]'] = 'Please bring a valid passport';
        $client->submit($saveForm, [], ['HTTPS' => 'on']);

        self::assertResponseRedirects('/vetting-type-hint?institution=' . self::OTHER_INSTITUTION);

        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::OTHER_INSTITUTION, (string) $client->getResponse()->getContent());
    }
}
