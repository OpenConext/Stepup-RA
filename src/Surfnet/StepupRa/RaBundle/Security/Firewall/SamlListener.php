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

namespace Surfnet\StepupRa\RaBundle\Security\Firewall;

use Exception;
use Psr\Log\LoggerInterface;
use SAML2_Response_Exception_PreconditionNotMetException as PreconditionNotMetException;
use Surfnet\SamlBundle\Http\Exception\AuthnFailedSamlResponseException;
use Surfnet\SamlBundle\Http\Exception\NoAuthnContextSamlResponseException;
use Surfnet\SamlBundle\SAML2\Response\Assertion\InResponseTo;
use Surfnet\StepupRa\RaBundle\Security\Authentication\SamlInteractionProvider;
use Surfnet\StepupRa\RaBundle\Security\Authentication\SessionHandler;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Security\Exception\UnmetLoaException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Twig_Environment as Twig;

class SamlListener implements ListenerInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(GetResponseEvent $event)
    {
        try {
            $this->handleEvent($event);
        } catch (\Exception $e) {
            /** @var SamlInteractionProvider $samlInteractionProvider */
            $samlInteractionProvider = $this->container->get('ra.security.authentication.saml');
            $samlInteractionProvider->reset();
        }
    }

    private function handleEvent(GetResponseEvent $event)
    {
        /** @var SessionHandler $sessionHandler */
        $sessionHandler = $this->container->get('ra.security.authentication.session_handler');

        // reinstate the token from the session. Could be expanded with logout check if needed
        if ($sessionHandler->hasBeenAuthenticated()) {
            $this->container->get('security.token_storage')->setToken($sessionHandler->getToken());
            return;
        }

        /** @var SamlInteractionProvider $samlInteractionProvider */
        $samlInteractionProvider = $this->container->get('ra.security.authentication.saml');

        if (!$samlInteractionProvider->isSamlAuthenticationInitiated()) {
            $sessionHandler->setCurrentRequestUri($event->getRequest()->getUri());
            $event->setResponse($samlInteractionProvider->initiateSamlRequest());

            return;
        }

        /** @var LoggerInterface $logger */
        $logger = $this->container->get('logger');

        $expectedInResponseTo = $sessionHandler->getRequestId();
        try {
            $assertion = $samlInteractionProvider->processSamlResponse($event->getRequest());
        } catch (PreconditionNotMetException $e) {
            $logger->notice(sprintf('SAML response precondition not met: "%s"', $e->getMessage()));
            return $this->setPreconditionExceptionResponse($e, $event);
        } catch (Exception $e) {
            $logger->error(sprintf('Failed SAMLResponse Parsing: "%s"', $e->getMessage()));
            throw new AuthenticationException('Failed SAMLResponse parsing', 0, $e);
        }

        if (!InResponseTo::assertEquals($assertion, $expectedInResponseTo)) {
            throw new AuthenticationException('Unknown or unexpected InResponseTo in SAMLResponse');
        }

        $loaResolutionService = $this->container->get('surfnet_stepup.service.loa_resolution');
        $loa = $loaResolutionService->getLoa($assertion->getAuthnContextClassRef());

        $token = new SamlToken($loa);
        $token->assertion = $assertion;

        try {
            /** @var AuthenticationProviderManager $authenticationManager */
            $authenticationManager = $this->container->get('security.authentication.manager');
            $authToken = $authenticationManager->authenticate($token);
            // for the current request
            $this->container->get('security.token_storage')->setToken($authToken);
            // for future requests
            $sessionHandler->setToken($authToken);

            $event->setResponse(new RedirectResponse($sessionHandler->getCurrentRequestUri()));
            return;
        } catch (AuthenticationException $failed) {
            $logger->error(sprintf('Authentication Failed, reason: "%s"', $failed->getMessage()));

            // By default deny authorization
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
        }
    }

    private function setPreconditionExceptionResponse(PreconditionNotMetException $exception, GetResponseEvent $event)
    {
        $template = null;

        if ($exception instanceof AuthnFailedSamlResponseException) {
            $template = 'SurfnetStepupRaRaBundle:Saml/Exception:authnFailed.html.twig';
        } elseif ($exception instanceof NoAuthnContextSamlResponseException) {
            $template = 'SurfnetStepupRaRaBundle:Saml/Exception:noAuthnContext.html.twig';
        } elseif ($exception instanceof UnmetLoaException) {
            $template = 'SurfnetStepupRaRaBundle:Saml/Exception:unmetLoa.html.twig';
        } else {
            $template = 'SurfnetStepupRaRaBundle:Saml/Exception:preconditionNotMet.html.twig';
        }

        /** @var Twig $twig */
        $twig = $this->container->get('twig');
        $html = $twig->render($template, ['exception' => $exception]);
        $event->setResponse(new Response($html, Response::HTTP_UNAUTHORIZED));
    }
}
