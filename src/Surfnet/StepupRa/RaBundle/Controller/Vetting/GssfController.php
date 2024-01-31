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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\SamlBundle\Http\XMLResponse;
use Surfnet\SamlBundle\SAML2\AuthnRequestFactory;
use Surfnet\SamlBundle\SAML2\Response\Assertion\InResponseTo;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Surfnet\StepupRa\RaBundle\Form\Type\InitiateGssfType;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
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
    /**
     * Initiates verification of a GSSF.
     *
     * @Template
     * @param string $procedureId
     * @param string $provider
     * @return array|Response
     */
    public function initiateAction($procedureId, $provider)
    {
        $this->assertSecondFactorEnabled($provider);

        $this->denyAccessUnlessGranted('ROLE_RA');

        $logger = $this->get('ra.procedure_logger')->forProcedure($procedureId);
        $logger->notice('Showing Initiate GSSF Verification Screen', ['provider' => $provider]);

        if (!$this->getVettingService()->hasProcedure($procedureId)) {
            $logger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        return $this->renderInitiateForm($procedureId, $this->getProvider($provider)->getName());
    }

    /**
     * @param string $procedureId
     * @param string $provider
     * @return array|Response
     */
    public function authenticateAction($procedureId, $provider)
    {
        $this->assertSecondFactorEnabled($provider);

        $this->denyAccessUnlessGranted('ROLE_RA');

        $logger = $this->get('ra.procedure_logger')->forProcedure($procedureId);
        $logger->notice('Generating GSSF verification request', ['provider' => $provider]);

        if (!$this->getVettingService()->hasProcedure($procedureId)) {
            $logger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        $provider = $this->getProvider($provider);

        $authnRequest = AuthnRequestFactory::createNewRequest(
            $provider->getServiceProvider(),
            $provider->getRemoteIdentityProvider()
        );

        /** @var \Surfnet\StepupRa\RaBundle\Service\VettingService $vettingService */
        $vettingService = $this->get('ra.service.vetting');
        $authnRequest->setSubject($vettingService->getSecondFactorIdentifier($procedureId));

        $stateHandler = $provider->getStateHandler();
        $stateHandler->setRequestId($authnRequest->getRequestId());

        /** @var \Surfnet\SamlBundle\Http\RedirectBinding $redirectBinding */
        $redirectBinding = $this->get('surfnet_saml.http.redirect_binding');

        $logger->notice(
            sprintf(
                'Sending AuthnRequest with request ID: "%s" to GSSP "%s" at "%s"',
                $authnRequest->getRequestId(),
                $provider->getName(),
                $provider->getRemoteIdentityProvider()->getSsoUrl()
            ),
            ['provider' => $provider]
        );

        $vettingService->startGssfVerification($procedureId);

        return $redirectBinding->createRedirectResponseFor($authnRequest);
    }

    /**
     * @param Request $httpRequest
     * @param string  $provider
     * @return array|Response
     */
    public function verifyAction(Request $httpRequest, $provider)
    {
        $this->assertSecondFactorEnabled($provider);

        $provider = $this->getProvider($provider);

        $this->get('logger')->notice(
            sprintf('Received GSSP "%s" SAMLResponse through Gateway, attempting to process', $provider->getName())
        );

        try {
            /** @var \Surfnet\SamlBundle\Http\PostBinding $postBinding */
            $postBinding = $this->get('surfnet_saml.http.post_binding');
            $assertion = $postBinding->processResponse(
                $httpRequest,
                $provider->getRemoteIdentityProvider(),
                $provider->getServiceProvider()
            );
        } catch (Exception $exception) {
            $provider->getStateHandler()->clear();
            $this->getLogger()->error(
                sprintf('Could not process received Response, error: "%s"', $exception->getMessage())
            );

            throw new BadRequestHttpException(
                'Could not process received SAML response, cannot return to vetting procedure'
            );
        }

        $expectedResponseTo = $provider->getStateHandler()->getRequestId();
        $provider->getStateHandler()->clear();

        if (!InResponseTo::assertEquals($assertion, $expectedResponseTo)) {
            $this->getLogger()->critical(sprintf(
                'Received Response with unexpected InResponseTo: %s',
                ($expectedResponseTo ? 'expected "' . $expectedResponseTo . '"' : ' no response expected')
            ));

            throw new BadRequestHttpException('Received unexpected SAML response, cannot return to vetting procedure');
        }

        $this->get('logger')->notice(
            sprintf('Processed GSSP "%s" SAMLResponse received through Gateway successfully', $provider->getName())
        );

        /** @var \Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary $attributeDictionary */
        $attributeDictionary = $this->get('surfnet_saml.saml.attribute_dictionary');
        $gssfId = $attributeDictionary->translate($assertion)->getNameID();

        /** @var \Surfnet\StepupRa\RaBundle\Service\VettingService $vettingService */
        $vettingService = $this->get('ra.service.vetting');
        $result = $vettingService->verifyGssfId($gssfId);

        if ($result->isSuccess()) {
            $this->getLogger()->notice('GSSP possession proven successfully');

            return $this->redirectToRoute('ra_vetting_verify_identity', ['procedureId' => $result->getProcedureId()]);
        }

        if (!$result->getProcedureId()) {
            // Should be unreachable statement, because the request ID is compared to the response ID a few lines before
            // this.
            throw new RuntimeException('Procedure ID for GSSF verification procedure could not be recovered.');
        }

        $this->getLogger()->notice(
            'Unable to prove possession of correct GSSF: ' .
            'GSSF ID registered in Self-Service does not match current GSSF ID'
        );

        return $this->renderInitiateForm(
            $result->getProcedureId(),
            $provider->getName(),
            ['gssfIdMismatch' => true]
        );
    }

    /**
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function metadataAction($provider)
    {
        $this->assertSecondFactorEnabled($provider);

        $provider = $this->getProvider($provider);

        /** @var \Surfnet\SamlBundle\Metadata\MetadataFactory $factory */
        $factory = $this->get('gssp.provider.' . $provider->getName() . '.metadata.factory');

        return new XMLResponse($factory->generate());
    }

    /**
     * @param string $provider
     * @return \Surfnet\StepupRa\SamlStepupProviderBundle\Provider\Provider
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getProvider($provider)
    {
        /** @var \Surfnet\StepupRa\SamlStepupProviderBundle\Provider\ProviderRepository $providerRepository */
        $providerRepository = $this->get('gssp.provider_repository');

        if (!$providerRepository->has($provider)) {
            $this->get('logger')->info(sprintf('Requested GSSP "%s" does not exist or is not registered', $provider));

            throw new NotFoundHttpException('Requested provider does not exist');
        }

        return $providerRepository->get($provider);
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    private function getLogger()
    {
        return $this->get('logger');
    }

    /**
     * @return VettingService
     */
    private function getVettingService()
    {
        return $this->get('ra.service.vetting');
    }

    /**
     * @param string $procedureId
     * @param string $provider
     * @param array  $parameters
     * @return Response
     */
    private function renderInitiateForm($procedureId, $provider, array $parameters = [])
    {
        $collection = $this->get("surfnet_stepup.provider.collection");
        $secondFactorConfig = $collection->getByIdentifier($provider);

        $form = $this->createForm(
            InitiateGssfType::class,
            null,
            [
                'procedureId' => $procedureId,
                'provider' => $provider,
                /** @Ignore from translation message extraction */
                'label' => $secondFactorConfig->getInitiate()
            ]
        );

        $templateParameters = array_merge(
            $parameters,
            [
                'form' => $form->createView(),
                'procedureId' => $procedureId,
                'provider' => $provider,
                'secondFactorConfig' => $secondFactorConfig
            ]
        );

        return $this->render('SurfnetStepupRaRaBundle:vetting/gssf:initiate.html.twig', $templateParameters);
    }
}
