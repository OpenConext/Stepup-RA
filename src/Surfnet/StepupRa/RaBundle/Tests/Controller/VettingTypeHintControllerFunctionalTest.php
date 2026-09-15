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

/**
 * Functional coverage for the real, multi-request bug reported against this flow: an RAA/SRAA
 * authorized for 2+ institutions selects a non-home institution via the select-institution form
 * (which re-renders in place and never changes the URL), then saves the vetting type hint. A
 * successful save must redirect to, and subsequently display, the institution that was actually
 * selected and saved - not the identity's home institution.
 */
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
        // Keep the same container (and therefore the same service overrides below) across all
        // requests in this test; by default the kernel reboots with a fresh container per request.
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

        // The GSSP session decorator resolves the current request (to build SP metadata URLs),
        // so a request must already be on the stack before the session is touched by loginUser().
        self::getContainer()->get('request_stack')->push(
            Request::create('https://ra.dev.openconext.local/', server: ['HTTPS' => 'on']),
        );
        $client->loginUser($authenticatedIdentity, 'saml_based');

        // Step 1: load the page; the dropdown defaults to the RAA's home institution.
        $client->request(Request::METHOD_GET, '/vetting-type-hint', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::HOME_INSTITUTION, (string) $client->getResponse()->getContent());

        // Step 2: switch to the other institution via the select-institution form. This form
        // submits in place and never changes the URL, which is exactly what the redirect fix
        // must not depend on.
        $crawler = $client->getCrawler();
        $selectForm = $crawler->filter('#select_institution_select_and_apply')->form();
        $selectForm['select_institution[institution]'] = self::OTHER_INSTITUTION;
        $client->submit($selectForm, [], ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::OTHER_INSTITUTION, (string) $client->getResponse()->getContent());

        // Step 3: save the vetting type hint text while the other institution is selected.
        $crawler = $client->getCrawler();
        $saveForm = $crawler->filter('#vetting_type_hint_continue')->form();
        $saveForm['vetting_type_hint[vetting_type_hint_en_GB]'] = 'Please bring a valid passport';
        $client->submit($saveForm, [], ['HTTPS' => 'on']);

        self::assertResponseRedirects('/vetting-type-hint?institution=' . self::OTHER_INSTITUTION);

        // Following the redirect must keep showing the institution that was just saved, not fall
        // back to the RAA's home institution.
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(self::OTHER_INSTITUTION, (string) $client->getResponse()->getContent());
    }
}
