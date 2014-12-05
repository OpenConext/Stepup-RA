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
use Surfnet\StepupRa\RaBundle\Command\StartVettingProcedureCommand;
use Surfnet\StepupRa\RaBundle\Dto\VettingProcedure;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Surfnet\StepupRa\RaBundle\Repository\VettingProcedureRepository;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorService;
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

            $procedure = VettingProcedure::start();
            $procedure->identityId = $secondFactor->identityId;
            $procedure->institution = $secondFactor->institution;
            $procedure->commonName = $secondFactor->commonName;
            $procedure->secondFactorType = $secondFactor->type;
            $procedure->expectedSecondFactorIdentifier = $secondFactor->secondFactorIdentifier;
            $procedure->registrationCode = $command->registrationCode;

            $this->getVettingProcedureRepository()->store($procedure);

            switch ($procedure->secondFactorType) {
                case 'yubikey':
                    return $this->redirectToRoute(
                        'vetting_yubikey_verify',
                        ['procedureUuid' => $procedure->uuid]
                    );
            }

            throw new RuntimeException(
                sprintf("Unexpected second factor type '%s'", $procedure->secondFactorType)
            );
        }

        return ['form' => $form->createView()];
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
