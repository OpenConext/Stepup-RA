<?php

/**
 * Copyright 2022 SURFnet B.V.
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
use Surfnet\StepupRa\RaBundle\Command\VettingTypeHintCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\VettingTypeHintType;
use Surfnet\StepupRa\RaBundle\Command\SelectInstitutionCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\SelectInstitutionType;
use Surfnet\StepupRa\RaBundle\Service\InstitutionListingService;
use Surfnet\StepupRa\RaBundle\Service\ProfileService;
use Surfnet\StepupRa\RaBundle\Service\VettingTypeHintService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class VettingTypeHintController extends Controller
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProfileService
     */
    private $profileService;

    /**
     * @var VettingTypeHintService
     */
    private $vettingTypeHintService;

    /**
     * @var InstitutionListingService
     */
    private $institutionListingService;

    /**
     * @var string[]
     */
    private $locales;

    public function __construct(
        LoggerInterface $logger,
        InstitutionListingService $institutionListingService,
        ProfileService $profileService,
        VettingTypeHintService $vettingTypeHintService,
        array $locales
    ) {
        $this->institutionListingService = $institutionListingService;
        $this->profileService = $profileService;
        $this->vettingTypeHintService = $vettingTypeHintService;
        $this->locales = $locales;
        $this->logger = $logger;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) Given the two forms being handled in this action, cc is higher.
     */
    public function vettingTypeHintAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);

        /** @var Identity $identity */
        $identity = $this->getUser();

        $profile = $this->profileService->findByIdentityId($identity->id);

        if ($this->isGranted('ROLE_SRAA')) {
            $institution =  $request->query->get('institution', $identity->institution);
            $choices = $this->institutionListingService->getAll();
        } elseif ($request->query->has('institution')) {
            $choices = $profile->getRaaInstitutions();
            $institution = $request->query->get('institution');
        } else {
            $choices = $profile->getRaaInstitutions();
            $institution = reset($choices);
        }

        // Only show the form if more than one institution where found.
        if (count($choices) > 1) {
            $selectInstitutionCommand = new SelectInstitutionCommand();
            $selectInstitutionCommand->institution = $institution;
            $selectInstitutionCommand->availableInstitutions = $choices;

            $form = $this->createForm(SelectInstitutionType::class, $selectInstitutionCommand);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $institution = $selectInstitutionCommand->institution;
            }
        }

        $command = new VettingTypeHintCommand();
        $command->institution = $institution;
        $command->identityId = $identity->id;
        $command->locales = $this->locales;
        $hints = $this->vettingTypeHintService->findBy($institution);
        if ($hints) {
            $command->setHints($hints->hints);
        }
        $hintForm = $this->createForm(VettingTypeHintType::class, $command);
        $hintForm->handleRequest($request);

        if ($hintForm->isSubmitted() && $hintForm->isValid()) {
            $this->logger->debug('Vetting type hint form submitted, start processing command');

            $success = $this->vettingTypeHintService->save($command);

            if ($success) {
                $this->addFlash('success', 'ra.vetting_type_hint.success');
            } else {
                $this->logger->debug('Vetting type hint saving failed, adding error to form');
                $this->addFlash('error', 'ra.vetting_type_hint.error');
            }
        }

        return $this->render(
            '@SurfnetStepupRaRa/vetting_type_hint/overview.html.twig',
            [
                'form' => isset($form) ? $form->createView() : null,
                'hintForm' => $hintForm->createView(),
                'institution' => $institution,
            ]
        );
    }
}
