<?php

/**
 * Copyright 2018 SURFnet B.V.
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
use Surfnet\StepupRa\RaBundle\Command\ChangeRaaInstitutionCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\RaaInstitutionSelectionType;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Security\Authorization\Voter\AllowedToSwitchInstitutionVoter;
use Surfnet\StepupRa\RaBundle\Service\InstitutionConfigurationOptionsService;
use Surfnet\StepupRa\RaBundle\Service\RaListingService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RaaController extends Controller
{
    public function selectInstitutionAction(Request $request)
    {
        $this->denyAccessUnlessGranted([AllowedToSwitchInstitutionVoter::RAA_SWITCHING]);

        /** @var SamlToken $token */
        $token  = $this->get('security.token_storage')->getToken();
        $logger = $this->get('logger');

        /** @var Identity $identity */
        $identity = $token->getUser();
        $institution = $identity->institution;

        $logger->notice(sprintf('Select Institution for RAA "%s"', $identity->id));

        $raaSwitcherOptions = $this
            ->getRaListingService()
            ->createChoiceListFor($token->getIdentityInstitution());

        $command = new ChangeRaaInstitutionCommand();
        $command->institution = $institution;
        $command->availableInstitutions = $raaSwitcherOptions;

        $form = $this->createForm(RaaInstitutionSelectionType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $institutionConfigurationOptions = $this->getInstitutionConfigurationOptionsService()
                ->getInstitutionConfigurationOptionsFor($command->institution);
            $token->changeInstitutionScope($command->institution, $institutionConfigurationOptions);

            $flashMessage = $this->get('translator')
                ->trans('ra.sraa.changed_institution', ['%institution%' => $command->institution]);
            $this->get('session')->getFlashBag()->add('success', $flashMessage);

            $logger->notice(sprintf(
                'RAA "%s" successfully switched to institution "%s"',
                $identity->id,
                $command->institution
            ));

            return $this->redirect($this->generateUrl('ra_vetting_search'));
        }

        $logger->notice(sprintf('Showing select institution form for RAA "%s"', $identity->id));

        return $this->render(
            // Reuse the SRAA switcher twig template
            'SurfnetStepupRaRaBundle:Sraa:selectInstitution.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @return RaListingService
     */
    private function getRaListingService()
    {
        return $this->get('ra.service.ra_listing');
    }

    /**
     * @return InstitutionConfigurationOptionsService
     */
    private function getInstitutionConfigurationOptionsService()
    {
        return $this->get('ra.service.institution_configuration_options');
    }
}
