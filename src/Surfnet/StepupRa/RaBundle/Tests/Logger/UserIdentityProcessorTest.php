<?php

/**
 * Copyright 2026 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Tests\Logger;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Logger\UserIdentityProcessor;
use Surfnet\StepupRa\RaBundle\Security\AuthenticatedIdentity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserIdentityProcessorTest extends TestCase
{
    #[Test]
    public function the_identity_id_is_added_to_the_record_for_an_authenticated_user(): void
    {
        $identity = Identity::fromData([
            'id' => 'identity-1',
            'name_id' => 'urn:collab:person:example.org:raa',
            'institution' => 'example.org',
            'email' => 'raa@example.org',
            'common_name' => 'Test RAA',
            'preferred_locale' => 'en_GB',
        ]);
        $user = new AuthenticatedIdentity($identity, new Loa(Loa::LOA_2, 'loa2'), ['ROLE_RAA']);

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));

        $record = (new UserIdentityProcessor($tokenStorage))($this->record());

        $this->assertSame('identity-1', $record->extra['user_id']);
    }

    #[Test]
    public function the_record_is_left_untouched_without_an_authenticated_user(): void
    {
        $record = (new UserIdentityProcessor(new TokenStorage()))($this->record());

        $this->assertArrayNotHasKey('user_id', $record->extra);
    }

    private function record(): LogRecord
    {
        return new LogRecord(new DateTimeImmutable(), 'app', Level::Notice, 'message');
    }
}
