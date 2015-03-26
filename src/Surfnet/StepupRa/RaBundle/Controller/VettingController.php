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

namespace Surfnet\StepupRa\RaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Command\StartVettingProcedureCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyIdentityCommand;
use Surfnet\StepupRa\RaBundle\Exception\DomainException;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorService;
use Surfnet\StepupRa\RaBundle\Service\VettingService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VettingController extends Controller
{
    /**
     * @Template
     * @param Request $request
     * @return array|Response
     */
    public function startProcedureAction(Request $request)
    {
        $command = new StartVettingProcedureCommand();

        $form = $this->createForm('ra_start_vetting_procedure', $command)->handleRequest($request);

        if ($form->isValid()) {
            $secondFactor = $this->getSecondFactorService()
                ->findVerifiedSecondFactorByRegistrationCode($command->registrationCode);

            if ($secondFactor === null) {
                $form->addError(new FormError('ra.form.start_vetting_procedure.unknown_registration_code'));

                return ['form' => $form->createView()];
            }

            /** @var SamlToken $token */
            $token = $this->get('security.token_storage')->getToken();
            $identity = $this->getIdentity();

            $command->authorityId = $identity->id;
            $command->authorityInstitution = $identity->institution;
            $command->authorityLoa = $token->getLoa();
            $command->secondFactor = $secondFactor;

            if (!$this->getVettingService()->isLoaSufficientToStartProcedure($command)) {
                $form->addError(new FormError('ra.form.start_vetting_procedure.loa_insufficient'));

                return ['form' => $form->createView()];
            }

            $procedureId = $this->getVettingService()->startProcedure($command);

            switch ($secondFactor->type) {
                case 'yubikey':
                    return $this->redirectToRoute(
                        'ra_vetting_yubikey_verify',
                        ['procedureId' => $procedureId]
                    );
                case 'sms':
                    return $this->redirectToRoute(
                        'ra_vetting_sms_send_challenge',
                        ['procedureId' => $procedureId]
                    );
                case 'tiqr':
                    return $this->redirectToRoute(
                        'ra_vetting_gssf_initiate',
                        ['procedureId' => $procedureId, 'provider' => $secondFactor->type]
                    );
            }

            throw new RuntimeException(
                sprintf("Unexpected second factor type '%s'", $secondFactor->type)
            );
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Template
     * @param Request $request
     * @param string $procedureId
     * @return array|Response
     */
    public function verifyIdentityAction(Request $request, $procedureId)
    {
        $command = new VerifyIdentityCommand();

        $form = $this->createForm('ra_verify_identity', $command)->handleRequest($request);
        $vettingService = $this->getVettingService();

        if ($form->isValid()) {
            try {
                $vettingService->verifyIdentity($procedureId, $command);

                try {
                    if ($vettingService->vet($procedureId)) {
                        return $this->redirectToRoute('ra_vetting_completed', ['procedureId' => $procedureId]);
                    }

                    $this->get('logger')->error('RA attempted to vet second factor, but the command failed');
                    $form->addError(new FormError('ra.verify_identity.second_factor_vetting_failed'));
                } catch (DomainException $e) {
                    $this->get('logger')->error(
                        "RA attempted to vet second factor, but the vetting procedure didn't allow it",
                        ['exception' => $e]
                    );
                    $form->addError(new FormError('ra.verify_identity.second_factor_vetting_failed'));
                }
            } catch (DomainException $e) {
                $this->get('logger')->error(
                    "RA attempted to verify identity, but the vetting procedure didn't allow it",
                    ['exception' => $e]
                );
                $form->addError(new FormError('ra.verify_identity.identity_verification_failed'));
            }
        }

        return [
            'commonName' => $vettingService->getIdentityCommonName($procedureId),
            'form' => $form->createView()
        ];
    }

    /**
     * @Template
     */
    public function vettingCompletedAction()
    {
        return [];
    }

    /**
     * @return SecondFactorService
     */
    private function getSecondFactorService()
    {
        return $this->get('ra.service.second_factor');
    }

    /**
     * @return VettingService
     */
    private function getVettingService()
    {
        return $this->get('ra.service.vetting');
    }

    /**
     * @return Identity
     */
    private function getIdentity()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }
}
