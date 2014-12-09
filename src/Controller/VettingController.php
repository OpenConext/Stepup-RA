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
use Surfnet\StepupMiddlewareClientBundle\Service\CommandService;
use Surfnet\StepupRa\RaBundle\Command\StartVettingProcedureCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyIdentityCommand;
use Surfnet\StepupRa\RaBundle\Identity\Command\VetSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\VettingProcedure;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Surfnet\StepupRa\RaBundle\Repository\VettingProcedureRepository;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

            $procedure = VettingProcedure::start($command->registrationCode, $secondFactor);

            $this->getVettingProcedureRepository()->store($procedure);

            switch ($procedure->getSecondFactor()->type) {
                case 'yubikey':
                    return $this->redirectToRoute(
                        'vetting_yubikey_verify',
                        ['procedureUuid' => $procedure->getUuid()]
                    );
            }

            throw new RuntimeException(
                sprintf("Unexpected second factor type '%s'", $procedure->getSecondFactor()->type)
            );
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Template
     * @param Request $request
     * @param VettingProcedure $procedure
     * @return array|Response
     */
    public function verifyIdentityAction(Request $request, VettingProcedure $procedure)
    {
        if (!$procedure->isReadyForIdentityVerification()) {
            # RA may not yet verify identity. Starting a login procedure doesn't help, so no AccessDeniedException.
            throw new AccessDeniedHttpException("Second factor must be verified before verifying a registrant's identity");
        }

        $command = new VerifyIdentityCommand();
        $command->commonName = $procedure->getSecondFactor()->commonName;

        $form = $this->createForm('ra_verify_identity', $command)->handleRequest($request);

        if ($form->isValid()) {
            $procedure->verifyIdentity($command->documentNumber);

            $vetCommand = new VetSecondFactorCommand();
            $vetCommand->identityId = $procedure->getSecondFactor()->identityId;
            $vetCommand->registrationCode = $procedure->getRegistrationCode();
            $vetCommand->secondFactorIdentifier = $procedure->getInputSecondFactorIdentifier();
            $vetCommand->documentNumber = $procedure->getDocumentNumber();
            $vetCommand->identityVerified = $procedure->isIdentityVerified();

            /** @var CommandService $service */
            $service = $this->get('surfnet_stepup_middleware_client.service.command');
            $result = $service->execute($vetCommand);

            if ($result->isSuccessful()) {
                $this->get('session')->getFlashBag()->add('success', 'ra.vetting.second_factor_vetted');

                return $this->redirectToRoute('vetting_search');
            }

            $form->addError(new FormError('ra.verify_identity.second_factor_vetting_failed'));
        }

        return [
            'commonName' => $procedure->getSecondFactor()->commonName,
            'form' => $form->createView()
        ];
    }

    /**
     * @return SecondFactorService
     */
    private function getSecondFactorService()
    {
        return $this->get('ra.service.second_factor');
    }

    /**
     * @return VettingProcedureRepository
     */
    private function getVettingProcedureRepository()
    {
        return $this->get('ra.repository.vetting_procedure');
    }
}
