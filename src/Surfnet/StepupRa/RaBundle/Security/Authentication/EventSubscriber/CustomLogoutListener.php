<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Security\Authentication\EventSubscriber;

use Psr\Log\LoggerInterface;
use Surfnet\StepupRa\RaBundle\Security\AuthenticatedIdentity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

readonly class CustomLogoutListener
{
    /**
     * @param array<string, string> $logoutRedirectUrl
     */
    public function __construct(
        private Security $security,
        private array $logoutRedirectUrl,
        private LoggerInterface $logger,
    ) {
    }

    #[AsEventListener(event: LogoutEvent::class)]
    public function onLogout(LogoutEvent $event): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof AuthenticatedIdentity) {
            $this->logger->debug('Logout requested without an authenticated user, session likely already expired');

            return;
        }

        $identity = $user->getIdentity();

        $logoutRedirectUrl = $this->logoutRedirectUrl[$identity->preferredLocale];

        $event->getRequest()->getSession()->invalidate();

        $response = new RedirectResponse($logoutRedirectUrl);

        $event->setResponse($response);
    }
}
