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
use Surfnet\StepupRa\RaBundle\Exception\LoaTooLowException;
use Surfnet\StepupRa\RaBundle\Exception\UnexpectedIssuerException;
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
     * @var \Surfnet\StepupRa\RaBundle\Security\Authentication\SamlAuthenticationStateHandler
     */
    private $samlAuthenticationStateHandler;

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
        SamlAuthenticationStateHandler $samlAuthenticationStateHandler,
        LoaResolutionService $loaResolutionService,
        Loa $requiredLoa
    ) {
        $this->serviceProvider                = $serviceProvider;
        $this->identityProvider               = $identityProvider;
        $this->redirectBinding                = $redirectBinding;
        $this->postBinding                    = $postBinding;
        $this->loaResolutionService           = $loaResolutionService;
        $this->requiredLoa                    = $requiredLoa;
        $this->samlAuthenticationStateHandler = $samlAuthenticationStateHandler;
    }

    /**
     * @return bool
     */
    public function isSamlAuthenticationInitiated()
    {
        return $this->samlAuthenticationStateHandler->hasRequestId();
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

        $this->samlAuthenticationStateHandler->setRequestId($authnRequest->getRequestId());

        return $this->redirectBinding->createRedirectResponseFor($authnRequest);
    }

    /**
     * @param Request $request
     * @return \SAML2\Assertion
     * @throws LoaTooLowException When required LoA is not met by response
     * @throws AuthenticationException When response LoA cannot be resolved
     * @throws UnexpectedIssuerException
     */
    public function processSamlResponse(Request $request)
    {
        /** @var \SAML2\Assertion $assertion */
        $assertion = $this->postBinding->processResponse(
            $request,
            $this->identityProvider,
            $this->serviceProvider
        );

        $this->samlAuthenticationStateHandler->clearRequestId();

        if ($assertion->getIssuer() !== $this->identityProvider->getEntityId()) {
            throw new UnexpectedIssuerException(sprintf(
                'Expected issuer to be configured remote IdP "%s", got "%s"',
                $this->identityProvider->getEntityId(),
                $assertion->getIssuer()
            ));
        }

        $authnContextClassRef = $assertion->getAuthnContextClassRef();
        if (!$this->loaResolutionService->hasLoa($authnContextClassRef)) {
            throw new AuthenticationException('Received SAML response with unresolvable LoA');
        }

        if (!$this->loaResolutionService->getLoa($authnContextClassRef)->canSatisfyLoa($this->requiredLoa)) {
            throw new LoaTooLowException(
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
        $this->samlAuthenticationStateHandler->clearRequestId();
    }
}
