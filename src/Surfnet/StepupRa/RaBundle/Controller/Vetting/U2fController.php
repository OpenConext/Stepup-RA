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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\StepupRa\RaBundle\Command\VerifyU2fPublicIdCommand;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Surfnet\StepupU2fBundle\Dto\SignResponse;
use Surfnet\StepupU2fBundle\Form\Type\VerifyDeviceAuthenticationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class U2fController extends SecondFactorController
{
    /**
     * @Template
     * @param Request $request
     * @param string  $procedureId
     * @return array|Response
     */
    public function startAuthenticationAction(Request $request, $procedureId)
    {
        $this->assertSecondFactorEnabled('u2f');

        $this->denyAccessUnlessGranted(['ROLE_RA']);

        $logger = $this->get('ra.procedure_logger')->forProcedure($procedureId);
        $logger->notice('Suggesting RA start U2F authentication');

        if (!$this->getVettingService()->hasProcedure($procedureId)) {
            $logger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw $this->createNotFoundException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        return ['procedureId' => $procedureId];
    }

    /**
     * @Template
     * @param Request $request
     * @param string  $procedureId
     * @return array|Response
     */
    public function authenticationAction(Request $request, $procedureId)
    {
        $this->assertSecondFactorEnabled('u2f');

        $this->denyAccessUnlessGranted(['ROLE_RA']);

        $logger = $this->get('ra.procedure_logger')->forProcedure($procedureId);
        $logger->notice('Requested U2F Verfication');

        if (!$this->getVettingService()->hasProcedure($procedureId)) {
            $logger->notice(sprintf('Vetting procedure "%s" not found', $procedureId));
            throw new NotFoundHttpException(sprintf('Vetting procedure "%s" not found', $procedureId));
        }

        $service = $this->getVettingService();
        $session = $this->get('ra.session.u2f');

        $result = $service->createU2fSignRequest($procedureId);

        if (!$result->wasSuccessful()) {
            $this->addFlash('error', 'ra.vetting.u2f.alert.error');

            return ['authenticationFailed' => true, 'procedureId' => $procedureId];
        }

        $signRequest = $result->getSignRequest();
        $signResponse = new SignResponse();

        $formAction = $this->generateUrl('ra_vetting_u2f_prove_possession', ['procedureId' => $procedureId]);
        $form = $this->createForm(
            VerifyDeviceAuthenticationType::class,
            $signResponse,
            ['sign_request' => $signRequest, 'action' => $formAction,]
        );

        $session->set('request', $signRequest);

        return ['form' => $form->createView()];
    }

    /**
     * @Template
     */
    public function provePossessionAction(Request $request, $procedureId)
    {
        $this->assertSecondFactorEnabled('u2f');

        $session = $this->get('ra.session.u2f');

        /** @var RegisterRequest $signRequest */
        $signRequest = $session->get('request');
        $signResponse = new SignResponse();

        $formAction = $this->generateUrl('ra_vetting_u2f_prove_possession', ['procedureId' => $procedureId]);
        $form = $this
            ->createForm(
                VerifyDeviceAuthenticationType::class,
                $signResponse,
                ['sign_request' => $signRequest, 'action' => $formAction]
            )
            ->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('SurfnetStepupRaRaBundle:vetting/u2f:authentication.html.twig', [
                'authenticationFailed' => true,
                'procedureId' => $procedureId,
            ]);
        }

        $service = $this->getVettingService();
        $result = $service->verifyU2fAuthentication($procedureId, $signRequest, $signResponse);

        if ($result->wasSuccessful()) {
            return $this->redirectToRoute('ra_vetting_verify_identity', ['procedureId' => $procedureId]);
        } elseif ($result->didDeviceReportAnyError()) {
            $this->addFlash('error', 'ra.vetting.u2f.alert.device_reported_an_error');
            return ['authenticationFailed' => true, 'procedureId' => $procedureId];
        } else {
            $this->addFlash('error', 'ra.vetting.u2f.alert.error');
            return ['authenticationFailed' => true, 'procedureId' => $procedureId];
        }
    }

    /**
     * @return VettingService
     */
    private function getVettingService()
    {
        return $this->get('ra.service.vetting');
    }
}
