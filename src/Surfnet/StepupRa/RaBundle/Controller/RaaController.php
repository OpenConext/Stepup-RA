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

use Psr\Log\LoggerInterface;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Service\InstitutionListingService;
use Surfnet\StepupRa\RaBundle\Service\ProfileService;
use Surfnet\StepupRa\RaBundle\Command\SelectInstitutionCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\SelectInstitutionType;
use Surfnet\StepupRa\RaBundle\Service\InstitutionConfigurationOptionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class RaaController extends AbstractController
{
    public function __construct(
        private readonly InstitutionConfigurationOptionsService $institutionConfigurationOptionsService,
        private readonly ProfileService $profileService,
        private readonly InstitutionListingService $institutionListingService,
        private readonly LoggerInterface $logger,
    )
    {
    }

    #[Route(
        path: '/institution-configuration',
        name: 'institution-configuration',
        methods: ['GET', 'POST'],
    )]
    public function institutionConfiguration(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RAA');
        $this->denyAccessUnlessGranted('ROLE_SRAA');

        /** @var Identity $identity */
        $identity = $this->getUser();

        $profile = $this->profileService->findByIdentityId($identity->id);

        if ($this->isGranted('ROLE_SRAA')) {
            $institution = $identity->institution;
            $choices = $this->institutionListingService->getAll();
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

        $this->logger->notice(sprintf('Opening the institution configuration for "%s"', $institution));

        // Load the configuration for the institution that was selected.
        $configuration = $this->institutionConfigurationOptionsService
            ->getInstitutionConfigurationOptionsFor($institution);

        if (!$configuration) {
            $this->logger->warning(sprintf('Unable to find the institution configuration for "%s"', $institution));
            throw new NotFoundHttpException('The institution configuration could not be found');
        }

        return $this->render(
            'institution_configuration/overview.html.twig',
            [
                'configuration' => (array)$configuration,
                'form' => isset($form) ? $form->createView() : null,
                'institution' => $institution,
            ],
        );
    }
}
