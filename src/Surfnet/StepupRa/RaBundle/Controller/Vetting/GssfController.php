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

namespace Surfnet\StepupRa\RaBundle\Controller\Vetting;

use Exception;
use JMS\TranslationBundle\Annotation\Ignore;
use Psr\Log\LoggerInterface;
use Surfnet\SamlBundle\Http\PostBinding;
use Surfnet\SamlBundle\Http\RedirectBinding;
use Surfnet\SamlBundle\Http\XMLResponse;
use Surfnet\SamlBundle\Metadata\MetadataFactory;
use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\SamlBundle\SAML2\AuthnRequestFactory;
use Surfnet\SamlBundle\SAML2\Response\Assertion\InResponseTo;
use Surfnet\StepupBundle\Value\Provider\ViewConfigCollection;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Surfnet\StepupRa\RaBundle\Form\Type\InitiateGssfType;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Surfnet\StepupRa\SamlStepupProviderBundle\Provider\Provider;
use Surfnet\StepupRa\SamlStepupProviderBundle\Provider\ProviderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Orchestrates verification of GSSFs (Generic SAML Second Factors) through GSSPs (Generic SAML Stepup Providers).
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class GssfController extends SecondFactorController
{
    public function __construct(
        private readonly ProviderRepository   $providerRepository,
        private readonly VettingService       $vettingService,
        private readonly LoggerInterface      $logger,
        private readonly RedirectBinding      $redirectBinding,
        private readonly PostBinding          $postBinding,
        private readonly AttributeDictionary  $attributeDictionary,
        private readonly ViewConfigCollection $collection,
    ) {
        parent::__construct($logger);
    }
    
    /**
     * Initiates verification of a GSSF.
     */
    public function initiate(string $procedureId, string $provider): Response
    {
        $this->assertSecondFactorEnabled($provider);

        $this->denyAccessUnlessGranted('ROLE_RA');

        $procedureLogger = $this->container->get('ra.procedure_logger')->forProcedure($procedureId);
        $procedureLogger->notice('Showing Initiate GSSF Verification Screen', ['provider' => $provider]);

        if (!$this->vettingService->hasProcedure($procedureId)) {
            $procedureLogger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        return $this->renderInitiateForm($procedureId, $this->getProvider($provider)->getName());
    }

    public function authenticate(string $procedureId, string $provider): Response
    {
        $this->assertSecondFactorEnabled($provider);

        $this->denyAccessUnlessGranted('ROLE_RA');

        $procedureLogger = $this->container->get('ra.procedure_logger')->forProcedure($procedureId);
        $procedureLogger->notice('Generating GSSF verification request', ['provider' => $provider]);

        if (!$this->vettingService->hasProcedure($procedureId)) {
            $procedureLogger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        $provider = $this->getProvider($provider);

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

    public function verify(Request $httpRequest, string $provider): Response
    {
        $this->assertSecondFactorEnabled($provider);

        $provider = $this->getProvider($provider);

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

        return $this->renderInitiateForm(
            $result->getProcedureId(),
            $provider->getName(),
            ['gssfIdMismatch' => true],
        );
    }

    public function metadata(string $provider): XMLResponse
    {
        $this->assertSecondFactorEnabled($provider);

        $provider = $this->getProvider($provider);

        /** @var MetadataFactory $factory */
        $factory = $this->container->get('gssp.provider.' . $provider->getName() . '.metadata.factory');

        return new XMLResponse($factory->generate());
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getProvider(string $provider): Provider
    {
        if (!$this->providerRepository->has($provider)) {
            $this->logger->info(sprintf('Requested GSSP "%s" does not exist or is not registered', $provider));

            throw new NotFoundHttpException('Requested provider does not exist');
        }

        return $this->providerRepository->get($provider);
    }
    private function renderInitiateForm(string $procedureId, string $provider, array $parameters = []): Response
    {
        
        $secondFactorConfig = $this->collection->getByIdentifier($provider);

        $form = $this->createForm(
            InitiateGssfType::class,
            null,
            [
                'procedureId' => $procedureId,
                'provider' => $provider,
                /** @Ignore from translation message extraction */
                'label' => $secondFactorConfig->getInitiate()
            ],
        );

        $templateParameters = array_merge(
            $parameters,
            [
                'form' => $form->createView(),
                'procedureId' => $procedureId,
                'provider' => $provider,
                'secondFactorConfig' => $secondFactorConfig
            ],
        );

        return $this->render('vetting/gssf/initiate.html.twig', $templateParameters);
    }
}
