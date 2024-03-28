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

use Exception;
use Psr\Log\LoggerInterface;
use Surfnet\SamlBundle\Http\PostBinding;
use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\SamlBundle\SAML2\Response\Assertion\InResponseTo;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorAssertionService;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Surfnet\StepupRa\SamlStepupProviderBundle\Provider\ProviderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Orchestrates verification of GSSFs (Generic SAML Second Factors) through GSSPs (Generic SAML Stepup Providers).
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class GssfVerifyController extends AbstractController
{
    public function __construct(
        private readonly ProviderRepository   $providerRepository,
        private readonly VettingService       $vettingService,
        private readonly LoggerInterface      $logger,
        private readonly PostBinding          $postBinding,
        private readonly AttributeDictionary  $attributeDictionary,
        private readonly GssfInitiateFormService $gssfInitiateFormService,
        private readonly SecondFactorAssertionService $secondFactorAssertionService,
    ) {
    }

    #[Route(
        path: '/vetting-procedure/gssf/{provider}/verify',
        name: 'ra_vetting_gssf_verify',
        methods: ['POST'],
    )]
    public function __invoke(Request $httpRequest, string $provider): Response
    {
        $this->secondFactorAssertionService->assertSecondFactorEnabled($provider);

        $provider = $this->providerRepository->get($provider);

        $this->logger->notice(
            sprintf('Received GSSP "%s" SAMLResponse through Gateway, attempting to process', $provider->getName()),
        );

        try {
            $assertion = $this->postBinding->processResponse(
                $httpRequest,
                $provider->getRemoteIdentityProvider(),
                $provider->getServiceProvider(),
            );
        } catch (Exception $exception) {
            $provider->getStateHandler()->clear();
            $this->logger->error(
                sprintf('Could not process received Response, error: "%s"', $exception->getMessage()),
            );

            throw new BadRequestHttpException(
                'Could not process received SAML response, cannot return to vetting procedure',
            );
        }

        $expectedResponseTo = $provider->getStateHandler()->getRequestId();
        $provider->getStateHandler()->clear();

        if (!InResponseTo::assertEquals($assertion, $expectedResponseTo)) {
            $this->logger->critical(sprintf(
                'Received Response with unexpected InResponseTo: %s',
                ($expectedResponseTo ? 'expected "' . $expectedResponseTo . '"' : ' no response expected'),
            ));

            throw new BadRequestHttpException('Received unexpected SAML response, cannot return to vetting procedure');
        }

        $this->logger->notice(
            sprintf('Processed GSSP "%s" SAMLResponse received through Gateway successfully', $provider->getName()),
        );
        
        $gssfId = $this->attributeDictionary->translate($assertion)->getNameID();

        $result = $this->vettingService->verifyGssfId($gssfId);

        if ($result->isSuccess()) {
            $this->logger->notice('GSSP possession proven successfully');

            return $this->redirectToRoute('ra_vetting_verify_identity', ['procedureId' => $result->getProcedureId()]);
        }

        if (!$result->getProcedureId()) {
            // Should be unreachable statement, because the request ID is compared to the response ID a few lines before
            // this.
            throw new RuntimeException('Procedure ID for GSSF verification procedure could not be recovered.');
        }

        $this->logger->notice(
            'Unable to prove possession of correct GSSF: ' .
            'GSSF ID registered in Self-Service does not match current GSSF ID',
        );

        return $this->gssfInitiateFormService->renderInitiateForm(
            $result->getProcedureId(),
            $provider->getName(),
            ['gssfIdMismatch' => true],
        );
    }
}
