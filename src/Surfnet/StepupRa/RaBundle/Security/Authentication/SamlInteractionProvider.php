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

namespace Surfnet\StepupRa\RaBundle\Security\Authentication;

use Surfnet\SamlBundle\Entity\IdentityProvider;
use Surfnet\SamlBundle\Entity\ServiceProvider;
use Surfnet\SamlBundle\Http\PostBinding;
use Surfnet\SamlBundle\Http\RedirectBinding;
use Surfnet\SamlBundle\SAML2\AuthnRequestFactory;
use Surfnet\StepupBundle\Service\LoaResolutionService;
use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupRa\RaBundle\Security\Exception\UnmetLoaException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SamlInteractionProvider
{
    /**
     * @var \Surfnet\SamlBundle\Entity\ServiceProvider
     */
    private $serviceProvider;

    /**
     * @var \Surfnet\SamlBundle\Entity\IdentityProvider
     */
    private $identityProvider;

    /**
     * @var \Surfnet\SamlBundle\Http\RedirectBinding
     */
    private $redirectBinding;

    /**
     * @var \Surfnet\SamlBundle\Http\PostBinding
     */
    private $postBinding;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Security\Authentication\SessionHandler
     */
    private $sessionHandler;

    /**
     * @var \Surfnet\StepupBundle\Service\LoaResolutionService
     */
    private $loaResolutionService;

    /**
     * @var \Surfnet\StepupBundle\Value\Loa
     */
    private $requiredLoa;

    public function __construct(
        ServiceProvider $serviceProvider,
        IdentityProvider $identityProvider,
        RedirectBinding $redirectBinding,
        PostBinding $postBinding,
        SessionHandler $sessionHandler,
        LoaResolutionService $loaResolutionService,
        Loa $requiredLoa
    ) {
        $this->serviceProvider      = $serviceProvider;
        $this->identityProvider     = $identityProvider;
        $this->redirectBinding      = $redirectBinding;
        $this->postBinding          = $postBinding;
        $this->sessionHandler       = $sessionHandler;
        $this->loaResolutionService = $loaResolutionService;
        $this->requiredLoa          = $requiredLoa;
    }

    /**
     * @return bool
     */
    public function isSamlAuthenticationInitiated()
    {
        return $this->sessionHandler->hasRequestId();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function initiateSamlRequest()
    {
        $authnRequest = AuthnRequestFactory::createNewRequest(
            $this->serviceProvider,
            $this->identityProvider
        );

        $authnRequest->setAuthenticationContextClassRef((string) $this->requiredLoa);

        $this->sessionHandler->setRequestId($authnRequest->getRequestId());

        return $this->redirectBinding->createRedirectResponseFor($authnRequest);
    }

    /**
     * @param Request $request
     * @return \SAML2_Assertion
     * @throws \Surfnet\StepupRa\RaBundle\Security\Exception\UnmetLoaException When required LoA is not met by response
     * @throws \SAML2_Response_Exception_PreconditionNotMetException
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException When response LoA cannot be resolved
     */
    public function processSamlResponse(Request $request)
    {
        /** @var \SAML2_Assertion $assertion */
        $assertion = $this->postBinding->processResponse(
            $request,
            $this->identityProvider,
            $this->serviceProvider
        );

        $this->sessionHandler->clearRequestId();

        $authnContextClassRef = $assertion->getAuthnContextClassRef();
        if (!$this->loaResolutionService->hasLoa($authnContextClassRef)) {
            throw new AuthenticationException('Received SAML response with unresolvable LoA');
        }

        if (!$this->loaResolutionService->getLoa($authnContextClassRef)->canSatisfyLoa($this->requiredLoa)) {
            throw new UnmetLoaException(
                sprintf(
                    "Gateway responded with LoA '%s', which is lower than required LoA '%s'",
                    $assertion->getAuthnContextClassRef(),
                    (string) $this->requiredLoa
                )
            );
        }

        return $assertion;
    }

    /**
     * Resets the SAML flow.
     */
    public function reset()
    {
        $this->sessionHandler->clearRequestId();
    }
}
