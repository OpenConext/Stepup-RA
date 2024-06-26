<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\EventListener;

use Psr\Log\LoggerInterface;

use Surfnet\StepupRa\RaBundle\Security\Authentication\AuthenticatedSessionStateHandler;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Session\SessionLifetimeGuard;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticatedUserListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface            $tokenStorage,
        private readonly SessionLifetimeGuard             $sessionLifetimeGuard,
        private readonly AuthenticatedSessionStateHandler $sessionStateHandler,
        private readonly LoggerInterface                  $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['updateLastInteractionMoment', 6],
        ];
    }

    public function updateLastInteractionMoment(RequestEvent $event): void
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null || !$this->sessionLifetimeGuard->sessionLifetimeWithinLimits($this->sessionStateHandler)) {
            return;
        }
        $this->logger->notice('Logged in user with a session within time limits detected, updating session state');

        // see ExplicitSessionTimeoutHandler for the rationale
        if ($event->getRequest()->getMethod() === 'GET') {
            $this->sessionStateHandler->setCurrentRequestUri($event->getRequest()->getRequestUri());
        }
        $this->sessionStateHandler->updateLastInteractionMoment();
    }
}
