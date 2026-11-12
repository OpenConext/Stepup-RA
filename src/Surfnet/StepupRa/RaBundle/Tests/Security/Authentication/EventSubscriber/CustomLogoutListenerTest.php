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

namespace Surfnet\StepupRa\RaBundle\Tests\Security\Authentication\EventSubscriber;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Security\AuthenticatedIdentity;
use Surfnet\StepupRa\RaBundle\Security\Authentication\EventSubscriber\CustomLogoutListener;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class CustomLogoutListenerTest extends TestCase
{
    private const REDIRECT_URLS = [
        'nl_NL' => 'https://example.org/nl',
        'en_GB' => 'https://example.org/en',
    ];

    #[Test]
    public function it_redirects_to_the_locale_specific_url_and_invalidates_the_session(): void
    {
        $identity = new Identity();
        $identity->preferredLocale = 'nl_NL';
        $user = new AuthenticatedIdentity($identity, $this->createStub(Loa::class));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('debug');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('invalidate');

        $event = $this->createEvent($session);
        $this->createListener($user, $logger)->onLogout($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(self::REDIRECT_URLS['nl_NL'], $response->getTargetUrl());
    }

    #[Test]
    public function it_skips_the_redirect_and_logs_when_there_is_no_authenticated_user(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('without an authenticated user'));

        $session = $this->createMock(Session::class);
        $session->expects($this->never())->method('invalidate');

        $event = $this->createEvent($session);
        $this->createListener(null, $logger)->onLogout($event);

        $this->assertNull($event->getResponse());
    }

    private function createListener(?AuthenticatedIdentity $user, LoggerInterface $logger): CustomLogoutListener
    {
        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($user);

        return new CustomLogoutListener($security, self::REDIRECT_URLS, $logger);
    }

    private function createEvent(Session $session): LogoutEvent
    {
        $request = new Request();
        $request->setSession($session);

        return new LogoutEvent($request, null);
    }
}
