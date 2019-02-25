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
use Surfnet\StepupRa\RaBundle\Command\SearchRaListingCommand;
use Surfnet\StepupRa\RaBundle\Command\SelectInstitutionCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\SelectInstitutionType;
use Surfnet\StepupRa\RaBundle\Service\InstitutionConfigurationOptionsService;
use Surfnet\StepupRa\RaBundle\Service\RaListingService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RaaController extends Controller
{
    public function institutionConfigurationAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);
        $token = $this->get('security.token_storage')->getToken();

        $logger = $this->get('logger');
        /** @var Identity $identity */
        $identity = $token->getUser();

        $institutionFilterOptions = $this
            ->getInstitutionConfigurationOptionsService()
            ->getAvailableInstitutionsFor($identity->institution);

        $selectRaaFilterOptions = $this
            ->getInstitutionConfigurationOptionsService()
            ->getAvailableSelectRaaInstitutionsFor($identity->institution);

        $command = new SearchRaListingCommand();
        $command->actorInstitution = $identity->institution;
        $command->actorId = $identity->id;
        $command->pageNumber = (int) $request->get('p', 1);
        $command->orderBy = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        // The options that will populate the institution filter choice list.
        $command->institutionFilterOptions = $institutionFilterOptions;
        $command->raInstitutionFilterOptions = $selectRaaFilterOptions;

        // Load the RA institutions for the identity that is logged in
        $raList = $this
            ->getRaListingService()
            ->search($command);

        /** @var \Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListing[] $raListings */
        $raListings = $raList->getElements();
        $institution = reset($raListings);

        $choices = [];
        foreach ($raListings as $item) {
            $choices[$item->raInstitution] = $item->raInstitution;
        }

        // SRAA's are usually not RAA for other institutions as they already are SRAA. Show the institution config
        // of the SRAAs SHO, and let her use the SRAA switcher in order to see config for different institutions.
        if (empty($raListings) && $this->isGranted('ROLE_SRAA')) {
            $institution = $identity->institution;
        } else {
            $institution = $institution->institution;
        }

        // Only show the form if more than one institutions where found.
        if (count($raListings) > 1) {
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
            '@SurfnetStepupRaRa/InstitutionConfiguration/overview.html.twig',
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
     * @return RaListingService
     */
    private function getRaListingService()
    {
        return $this->get('ra.service.ra_listing');
    }

}
