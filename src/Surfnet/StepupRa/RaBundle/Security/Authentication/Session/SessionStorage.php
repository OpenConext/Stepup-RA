<?php

/**
 * Copyright 2014 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Security\Authentication\Session;

use Surfnet\StepupRa\RaBundle\Exception\LogicException;
use Surfnet\StepupRa\RaBundle\Security\Authentication\AuthenticatedSessionStateHandler;
use Surfnet\StepupRa\RaBundle\Security\Authentication\SamlAuthenticationStateHandler;
use Surfnet\StepupRa\RaBundle\Value\DateTime;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionStorage implements AuthenticatedSessionStateHandler, SamlAuthenticationStateHandler
{
    /**
     * Session keys
     */
    const AUTH_SESSION_KEY = '__auth/';
    const SAML_SESSION_KEY = '__saml/';

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function logAuthenticationMoment()
    {
        if ($this->isAuthenticationMomentLogged()) {
            throw new LogicException('Cannot log authentication moment as an authentication moment is already logged');
        }

        $this->session->set(self::AUTH_SESSION_KEY . 'authenticated_at', DateTime::now()->format(DateTime::FORMAT));
        $this->updateLastInteractionMoment();
    }

    public function isAuthenticationMomentLogged()
    {
        return $this->session->get(self::AUTH_SESSION_KEY . 'authenticated_at', null) !== null;
    }

    public function getAuthenticationMoment()
    {
        if (!$this->isAuthenticationMomentLogged()) {
            throw new LogicException('Cannot get last authentication moment as no authentication has been set');
        }

        return DateTime::fromString($this->session->get(self::AUTH_SESSION_KEY . 'authenticated_at'));
    }

    public function updateLastInteractionMoment()
    {
        $this->session->set(self::AUTH_SESSION_KEY . 'last_interaction', DateTime::now()->format(DateTime::FORMAT));
    }

    public function hasSeenInteraction()
    {
        return $this->session->get(self::AUTH_SESSION_KEY . 'last_interaction', null) !== null;
    }

    public function getLastInteractionMoment()
    {
        if (!$this->hasSeenInteraction()) {
            throw new LogicException('Cannot get last interaction moment as we have not seen any interaction');
        }

        return DateTime::fromString($this->session->get(self::AUTH_SESSION_KEY . 'last_interaction'));
    }

    public function setCurrentRequestUri($uri)
    {
        $this->session->set(self::AUTH_SESSION_KEY . 'current_uri', $uri);
    }

    public function getCurrentRequestUri()
    {
        $uri = $this->session->get(self::AUTH_SESSION_KEY . 'current_uri');
        $this->session->remove(self::AUTH_SESSION_KEY . 'current_uri');

        return $uri;
    }

    public function getRequestId()
    {
        return $this->session->get(self::SAML_SESSION_KEY . 'request_id');
    }

    public function setRequestId($requestId)
    {
        $this->session->set(self::SAML_SESSION_KEY . 'request_id', $requestId);
    }

    public function hasRequestId()
    {
        return $this->session->has(self::SAML_SESSION_KEY. 'request_id');
    }

    public function clearRequestId()
    {
        $this->session->remove(self::SAML_SESSION_KEY . 'request_id');
    }

    public function invalidate()
    {
        $this->session->invalidate();
    }

    public function migrate()
    {
        $this->session->migrate();
    }
}
