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
use Surfnet\StepupRa\RaBundle\Service\InstitutionListingService;
use Surfnet\StepupRa\RaBundle\Service\ProfileService;
use Surfnet\StepupRa\RaBundle\Command\SelectInstitutionCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\SelectInstitutionType;
use Surfnet\StepupRa\RaBundle\Service\InstitutionConfigurationOptionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RaaController extends AbstractController
{
    public function institutionConfigurationAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_RAA');
        $this->denyAccessUnlessGranted('ROLE_SRAA');

        $logger = $this->get('logger');
        /** @var Identity $identity */
        $identity = $this->getUser();

        $profile = $this->getProfileService()->findByIdentityId($identity->id);

        if ($this->isGranted('ROLE_SRAA')) {
            $institution = $identity->institution;
            $choices = $this->getInstitutionListingService()->getAll();
        } else {
            $choices = $profile->getRaaInstitutions();
            $institution = reset($choices);
        }

        // Only show the form if more than one institutions where found.
        if (count($choices) > 1) {
            $command = new SelectInstitutionCommand();
            $command->institution = $institution;
            $command->availableInstitutions = $choices;

            $form = $this->createForm(SelectInstitutionType::class, $command);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $institution = $command->institution;
            }
        }

        $logger->notice(sprintf('Opening the institution configuration for "%s"', $institution));

        // Load the configuration for the institution that was selected.
        $configuration = $this->getInstitutionConfigurationOptionsService()
            ->getInstitutionConfigurationOptionsFor($institution);

        if (!$configuration) {
            $logger->warning(sprintf('Unable to find the institution configuration for "%s"', $institution));
            return $this->createNotFoundException('The institution configuration could not be found');
        }

        return $this->render(
            '@SurfnetStepupRaRa/institution_configuration/overview.html.twig',
            [
                'configuration' => (array)$configuration,
                'form' => isset($form) ? $form->createView() : null,
                'institution' => $institution,
            ]
        );
    }

    /**
     * @return InstitutionConfigurationOptionsService
     */
    private function getInstitutionConfigurationOptionsService()
    {
        return $this->get('ra.service.institution_configuration_options');
    }

    /**
     * @return ProfileService
     */
    private function getProfileService()
    {
        return $this->get('ra.service.profile');
    }

    /**
     * @return InstitutionListingService
     */
    private function getInstitutionListingService()
    {
        return $this->get('ra.service.institution_listing');
    }
}
