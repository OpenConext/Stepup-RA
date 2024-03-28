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

use Surfnet\StepupRa\RaBundle\Logger\ProcedureAwareLogger;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorAssertionService;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Surfnet\StepupRa\SamlStepupProviderBundle\Provider\ProviderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Orchestrates verification of GSSFs (Generic SAML Second Factors) through GSSPs (Generic SAML Stepup Providers).
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class GssfInitiateVerificationController extends AbstractController
{
    public function __construct(
        private readonly ProviderRepository   $providerRepository,
        private readonly VettingService       $vettingService,
        private readonly GssfInitiateFormService $gssfInitiateFormService,
        private readonly SecondFactorAssertionService $secondFactorAssertionService,
        private readonly ProcedureAwareLogger $procedureAwareLogger,
    ) {
    }
    
    /**
     * Initiates verification of a GSSF.
     */
    #[Route(
        path: '/vetting-procedure/{procedureId}/gssf/{provider}/initiate-verification',
        name: 'ra_vetting_gssf_initiate',
        methods: ['GET'],
    )]
    #[IsGranted('ROLE_RA')]
    public function __invoke(string $procedureId, string $provider): Response
    {
        $this->secondFactorAssertionService->assertSecondFactorEnabled($provider);

        $procedureLogger = $this->procedureAwareLogger->forProcedure($procedureId);
        $procedureLogger->notice('Showing Initiate GSSF Verification Screen', ['provider' => $provider]);

        if (!$this->vettingService->hasProcedure($procedureId)) {
            $procedureLogger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        return $this->gssfInitiateFormService->renderInitiateForm(
            procedureId: $procedureId,
            providerName: $this->providerRepository->get($provider)->getName(),
        );
    }
}
