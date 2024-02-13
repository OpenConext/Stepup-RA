<?php

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

namespace Surfnet\StepupRa\RaBundle\Controller\Vetting\Gssf;

use Surfnet\SamlBundle\Http\RedirectBinding;
use Surfnet\SamlBundle\SAML2\AuthnRequestFactory;
use Surfnet\StepupRa\RaBundle\Logger\ProcedureAwareLogger;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorAssertionService;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Surfnet\StepupRa\SamlStepupProviderBundle\Provider\ProviderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Orchestrates verification of GSSFs (Generic SAML Second Factors) through GSSPs (Generic SAML Stepup Providers).
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class GssfAuthenticateController extends AbstractController
{
    public function __construct(
        private readonly ProviderRepository   $providerRepository,
        private readonly VettingService       $vettingService,
        private readonly RedirectBinding      $redirectBinding,
        private readonly SecondFactorAssertionService $secondFactorAssertionService,
        private readonly ProcedureAwareLogger $procedureAwareLogger,
    ) {
    }
    
    #[Route(
        path: '/vetting-procedure/{procedureId}/gssf/{provider}/authenticate',
        name: 'ra_vetting_gssf_authenticate',
        methods: ['POST'],
    )]
    public function __invoke(string $procedureId, string $provider): Response
    {
        $this->secondFactorAssertionService->assertSecondFactorEnabled($provider);

        $this->denyAccessUnlessGranted('ROLE_RA');

        $procedureLogger = $this->procedureAwareLogger->forProcedure($procedureId);
        $procedureLogger->notice('Generating GSSF verification request', ['provider' => $provider]);

        if (!$this->vettingService->hasProcedure($procedureId)) {
            $procedureLogger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        $provider = $this->providerRepository->get($provider);

        $authnRequest = AuthnRequestFactory::createNewRequest(
            $provider->getServiceProvider(),
            $provider->getRemoteIdentityProvider(),
        );

        $authnRequest->setSubject($this->vettingService->getSecondFactorIdentifier($procedureId));

        $stateHandler = $provider->getStateHandler();
        $stateHandler->setRequestId($authnRequest->getRequestId());

        $procedureLogger->notice(
            sprintf(
                'Sending AuthnRequest with request ID: "%s" to GSSP "%s" at "%s"',
                $authnRequest->getRequestId(),
                $provider->getName(),
                $provider->getRemoteIdentityProvider()->getSsoUrl(),
            ),
            ['provider' => $provider],
        );

        $this->vettingService->startGssfVerification($procedureId);

        return $this->redirectBinding->createResponseFor($authnRequest);
    }

}
