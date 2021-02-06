<?php

/**
 * Copyright 2016 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Security\Authentication\Handler;

use Surfnet\SamlBundle\Monolog\SamlAuthenticationLogger;
use Surfnet\SamlBundle\SAML2\Response\Assertion\InResponseTo;
use Surfnet\StepupBundle\Service\LoaResolutionService;
use Surfnet\StepupRa\RaBundle\Security\Authentication\AuthenticatedSessionStateHandler;
use Surfnet\StepupRa\RaBundle\Security\Authentication\SamlAuthenticationStateHandler;
use Surfnet\StepupRa\RaBundle\Security\Authentication\SamlInteractionProvider;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) SamlResponse parsing, validation authentication and error handling
 *                                                 requires quite a few classes as it is fairly complex.
 */
class ProcessSamlAuthenticationHandler implements AuthenticationHandler
{
    /**
     * @var AuthenticationHandler|null
     */
    private $nextHandler = null;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var SamlInteractionProvider
     */
    private $samlInteractionProvider;

    /**
     * @var SamlAuthenticationStateHandler
     */
    private $authenticationStateHandler;

    /**
     * @var AuthenticatedSessionStateHandler
     */
    private $authenticatedSession;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var LoaResolutionService
     */
    private $loaResolutionService;

    /**
     * @var SamlAuthenticationLogger
     */
    private $authenticationLogger;

    /**
     * @var EngineInterface
     */
    private $templating;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SamlInteractionProvider $samlInteractionProvider,
        SamlAuthenticationStateHandler $authenticationStateHandler,
        AuthenticatedSessionStateHandler $authenticatedSession,
        AuthenticationManagerInterface $authenticationManager,
        LoaResolutionService $loaResolutionService,
        SamlAuthenticationLogger $authenticationLogger,
        EngineInterface $templating
    ) {
        $this->tokenStorage               = $tokenStorage;
        $this->samlInteractionProvider    = $samlInteractionProvider;
        $this->authenticationStateHandler = $authenticationStateHandler;
        $this->authenticatedSession       = $authenticatedSession;
        $this->authenticationManager      = $authenticationManager;
        $this->loaResolutionService       = $loaResolutionService;
        $this->authenticationLogger       = $authenticationLogger;
        $this->templating                 = $templating;
    }

    public function process(GetResponseEvent $event): void
    {
        if ($this->tokenStorage->getToken() === null
            && $this->samlInteractionProvider->isSamlAuthenticationInitiated()
        ) {
            $expectedInResponseTo = $this->authenticationStateHandler->getRequestId();
            $logger               = $this->authenticationLogger->forAuthentication($expectedInResponseTo);

            $logger->notice('No authenticated user and AuthnRequest pending, attempting to process SamlResponse');

            $assertion = $this->samlInteractionProvider->processSamlResponse($event->getRequest());

            if (!InResponseTo::assertEquals($assertion, $expectedInResponseTo)) {
                $logger->error('Unknown or unexpected InResponseTo in SAMLResponse');

                throw new AuthenticationException('Unknown or unexpected InResponseTo in SAMLResponse');
            }

            $logger->notice('Successfully processed SAMLResponse, attempting to authenticate');

            $loa = $this->loaResolutionService->getLoa($assertion->getAuthnContextClassRef());

            $token            = new SamlToken($loa);
            $token->assertion = $assertion;

            $authToken = $this->authenticationManager->authenticate($token);

            $this->authenticatedSession->logAuthenticationMoment();
            $this->tokenStorage->setToken($authToken);

            // migrate the session to prevent session hijacking
            $this->authenticatedSession->migrate();

            $event->setResponse(new RedirectResponse($this->authenticatedSession->getCurrentRequestUri()));

            $logger->notice('Authentication succeeded, redirecting to original location');

            return;
        }

        if ($this->nextHandler !== null) {
            $this->nextHandler->process($event);
        }
    }

    public function setNext(AuthenticationHandler $handler): void
    {
        $this->nextHandler = $handler;
    }
}
