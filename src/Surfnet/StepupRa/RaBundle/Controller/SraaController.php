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

use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Command\SelectInstitutionCommand;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SraaController extends Controller
{
    public function selectInstitutionAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_SRAA']);

        $logger = $this->get('logger');
        /** @var Identity $identity */
        $identity = $this->get('security.token_storage')->getToken()->getUser();

        $logger->notice(sprintf('Select Institution for SRAA "%s"', $identity->id));

        $command = new SelectInstitutionCommand();
        $command->institution = $identity->institution;

        $form = $this->createForm('sraa_institution_select', $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $newInstitution = $command->institution;
            $identity->institution = $newInstitution;

            /** @var SamlToken $token */
            $tokenStorage = $this->get('security.token_storage');
            $token        = $tokenStorage->getToken();

            $institutionConfigurationOptionsService = $this->get('ra.service.institution_configuration_options');
            $institutionConfigurationOptions = $institutionConfigurationOptionsService
                ->getInstitutionConfigurationOptionsFor($newInstitution);

            // The institution configuration options need to be updated to correctly acquire the context of the
            // selected institution
            $token->setInstitutionConfigurationOptions($institutionConfigurationOptions);
            $tokenStorage->setToken($token);

            $flashMessage = $this->get('translator')
                ->trans('ra.sraa.changed_institution', ['%institution%' => $newInstitution]);

            $this->get('session')->getFlashBag()->add('success', $flashMessage);

            $logger->notice(sprintf(
                'SRAA "%s successfully switched to institution "%s"',
                $identity->id,
                $newInstitution
            ));

            return $this->redirect($this->generateUrl('ra_vetting_search'));
        }

        $logger->notice(sprintf('Showing select institution form for SRAA "%s"', $identity->id));

        return $this->render(
            'SurfnetStepupRaRaBundle:Sraa:selectInstitution.html.twig',
            ['form' => $form->createView()]
        );
    }
}
