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

use SAML2\Assertion;
use Surfnet\SamlBundle\Entity\IdentityProvider;
use Surfnet\SamlBundle\Entity\ServiceProvider;
use Surfnet\SamlBundle\Http\PostBinding;
use Surfnet\SamlBundle\Http\RedirectBinding;
use Surfnet\SamlBundle\SAML2\AuthnRequestFactory;
use Surfnet\StepupBundle\Service\LoaResolutionService;
use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupRa\RaBundle\Exception\LoaTooLowException;
use Surfnet\StepupRa\RaBundle\Exception\UnexpectedIssuerException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SamlInteractionProvider
{
    public function __construct(
        private readonly ServiceProvider $serviceProvider,
        private readonly IdentityProvider $identityProvider,
        private readonly RedirectBinding $redirectBinding,
        private readonly PostBinding $postBinding,
        private readonly SamlAuthenticationStateHandler $samlAuthenticationStateHandler,
        private readonly LoaResolutionService $loaResolutionService,
        private readonly Loa $requiredLoa,
    ) {
    }

    /**
     * @return bool
     */
    public function isSamlAuthenticationInitiated()
    {
        return $this->samlAuthenticationStateHandler->hasRequestId();
    }

    public function initiateSamlRequest(): RedirectResponse
    {
        $authnRequest = AuthnRequestFactory::createNewRequest(
            $this->serviceProvider,
            $this->identityProvider,
        );

        $authnRequest->setAuthenticationContextClassRef((string) $this->requiredLoa);

        $this->samlAuthenticationStateHandler->setRequestId($authnRequest->getRequestId());

        return $this->redirectBinding->createResponseFor($authnRequest);
    }

    /**
     * @throws LoaTooLowException When required LoA is not met by response
     * @throws AuthenticationException When response LoA cannot be resolved
     * @throws UnexpectedIssuerException
     */
    public function processSamlResponse(Request $request): Assertion
    {
        $assertion = $this->postBinding->processResponse(
            $request,
            $this->identityProvider,
            $this->serviceProvider,
        );

        $this->samlAuthenticationStateHandler->clearRequestId();

        if ($assertion->getIssuer() !== $this->identityProvider->getEntityId()) {
            throw new UnexpectedIssuerException(sprintf(
                'Expected issuer to be configured remote IdP "%s", got "%s"',
                $this->identityProvider->getEntityId(),
                $assertion->getIssuer(),
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
                    $this->requiredLoa,
                ),
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
