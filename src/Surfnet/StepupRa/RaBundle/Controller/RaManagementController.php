<?php

/**
 * Copyright 2015 SURFnet bv
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

use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaCandidateInstitution;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListing;
use Surfnet\StepupRa\RaBundle\Command\AccreditCandidateCommand;
use Surfnet\StepupRa\RaBundle\Command\AmendRegistrationAuthorityInformationCommand;
use Surfnet\StepupRa\RaBundle\Command\RetractRegistrationAuthorityCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaCandidatesCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaListingCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\AmendRegistrationAuthorityInformationType;
use Surfnet\StepupRa\RaBundle\Form\Type\CreateRaType;
use Surfnet\StepupRa\RaBundle\Form\Type\RetractRegistrationAuthorityType;
use Surfnet\StepupRa\RaBundle\Form\Type\SearchRaCandidatesType;
use Surfnet\StepupRa\RaBundle\Form\Type\SearchRaListingType;
use Surfnet\StepupRa\RaBundle\Service\RaCandidateService;
use Surfnet\StepupRa\RaBundle\Service\RaListingService;
use Surfnet\StepupRa\RaBundle\Service\RaService;
use Surfnet\StepupRa\RaBundle\Value\RoleAtInstitution;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RaManagementController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RaListingService $raListingService,
        private readonly RaCandidateService $raCandidateService,
        private readonly PaginatorInterface $paginator,
        private readonly TranslatorInterface $translator,
        private readonly RaService $raService,
    ) {
    }

    #[Route(
        path: '/management/ra',
        name: 'ra_management_manage',
        methods: ['GET'],
    )]
    #[IsGranted('ROLE_RAA')]
    public function manage(Request $request): Response
    {
        $institution = $this->getUser()->getInstitution();
        $this->logger->notice(sprintf('Loading overview of RA(A)s for institution "%s"', $institution));

        $identity = $this->getUser()->getIdentity();

        $command = new SearchRaListingCommand();
        $command->actorId = $identity->id;
        $command->pageNumber = (int) $request->get('p', 1);
        $command->orderBy = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        // The options that will populate the institution filter choice list.
        $raList = $this->raListingService->search($command);
        $command->institutionFilterOptions = $raList->getFilterOption('institution');
        $command->raInstitutionFilterOptions = $raList->getFilterOption('raInstitution');

        $form = $this->createForm(SearchRaListingType::class, $command, ['method' => 'get']);
        $form->handleRequest($request);

        $raList = $this->raListingService->search($command);

        $pagination = $this->paginator->paginate(
            $raList->getTotalItems() > 0 ? $raList->getElements() : [],
            $raList->getCurrentPage(),
            $raList->getItemsPerPage(),
        );
        $pagination->setTotalItemCount($raList->getTotalItems());

        $this->logger->notice(sprintf(
            'Searching for RA(A)s yielded "%d" results',
            $raList->getTotalItems(),
        ));

        /** @var RaListing[] $raListings */
        $raListings = $raList->getElements();

        return $this->render(
            'ra_management/manage.html.twig',
            [
                'form' => $form->createView(),
                'raList' => $raListings,
                'numberOfResults' => $raList->getTotalItems(),
                'pagination' => $pagination,
            ],
        );
    }

    #[Route(
        path: '/management/search-ra-candidate',
        name: 'ra_management_ra_candidate_search',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('ROLE_RAA')]
    public function raCandidateSearch(Request $request): Response
    {
        $identity = $this->getUser()->getIdentity();
        $institution = $identity->institution;

        $this->logger->notice(sprintf('Searching for RaCandidates within institution "%s"', $institution));

        $command                   = new SearchRaCandidatesCommand();
        $command->actorId          = $identity->id;
        $command->actorInstitution = $institution;
        $command->raInstitution    = null;
        $command->pageNumber       = (int) $request->get('p', 1);
        $command->orderBy          = $request->get('orderBy');
        $command->orderDirection   = $request->get('orderDirection');

        $raCandidateList = $this->raCandidateService->search($command);

        // The options that will populate the institution filter choice list.
        $command->institutionFilterOptions = $raCandidateList->getFilterOption('institution');

        $form = $this->createForm(SearchRaCandidatesType::class, $command, ['method' => 'get']);
        $form->handleRequest($request);

        $raCandidateList = $this->raCandidateService->search($command);

        $pagination = $this->paginator->paginate(
            $raCandidateList->getTotalItems() > 0 ? $raCandidateList->getElements() : [],
            $raCandidateList->getCurrentPage(),
            $raCandidateList->getItemsPerPage(),
        );
        $pagination->setTotalItemCount($raCandidateList->getTotalItems());

        $this->logger->notice(sprintf(
            'Searching for RaCandidates within institution "%s" yielded "%s" results',
            $institution,
            $raCandidateList->getTotalItems(),
        ));

        return $this->render(
            'ra_management/ra_candidate_overview.html.twig',
            [
                'form'         => $form->createView(),
                'raCandidates' => $raCandidateList,
                'pagination'   => $pagination
            ],
        );
    }

    #[Route(
        path: '/management/create-ra/{identityId}',
        name: 'ra_management_create_ra',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('ROLE_RAA')]
    public function createRa(Request $request): Response
    {
        $this->logger->notice('Page for Accreditation of Identity to Ra or Raa requested');
        $identityId = $request->get('identityId');

        $raCandidate = $this->raCandidateService->getRaCandidate($identityId, $this->getUser()->getIdentity()->id);

        if (! isset($raCandidate->raCandidate)) {
            $this->logger->warning(sprintf('RaCandidate based on identity "%s" not found', $identityId));
            throw new NotFoundHttpException();
        }

        $options = array_map(fn(RaCandidateInstitution $institution) => $institution->institution, $raCandidate->institutions->getElements());
        $selectOptions = array_combine($options, $options);

        $command = new AccreditCandidateCommand();
        $command->identityId = $identityId;
        $command->institution = $raCandidate->raCandidate->institution;
        $command->roleAtInstitution = new RoleAtInstitution();
        $command->roleAtInstitution->setInstitution($this->getUser()->getIdentity()->institution);
        $command->availableInstitutions = $selectOptions;

        $form = $this->createForm(CreateRaType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->debug('Accreditation form submitted, start processing command');

            $success = $this->raCandidateService->accreditCandidate($command);

            if ($success) {
                $this->addFlash(
                    'success',
                    $this->translator->trans('ra.management.create_ra.identity_accredited'),
                );

                $this->logger->debug('Identity Accredited, redirecting to candidate overview');
                return $this->redirectToRoute('ra_management_ra_candidate_search');
            }

            $this->logger->debug('Identity Accreditation failed, adding error to form');
            $this->addFlash('error', 'ra.management.create_ra.error.middleware_command_failed');
        }

        return $this->render('ra_management/create_ra.html.twig', [
            'raCandidate' => $raCandidate->raCandidate,
            'form'        => $form->createView()
        ]);
    }

    /**
     * @param         $identityId
     * @param         $raInstitution
     */
    #[Route(
        path: '/management/amend-ra-information/{identityId}/{raInstitution}',
        name: 'ra_management_amend_ra_information',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('ROLE_RAA')]
    public function amendRaInformation(Request $request, $identityId, $raInstitution): Response
    {
        $this->logger->notice(sprintf("Loading information amendment form for RA(A) '%s'", $identityId));

        $raListing = $this->raListingService->get($identityId, $raInstitution, $this->getUser()->getIdentity()->id);

        if (!$raListing) {
            $this->logger->warning(sprintf("RA listing for identity ID '%s' not found", $identityId));
            throw new NotFoundHttpException(sprintf("RA listing for identity ID '%s' not found", $identityId));
        }

        $command = new AmendRegistrationAuthorityInformationCommand();
        $command->identityId = $raListing->identityId;
        $command->location = $raListing->location;
        $command->contactInformation = $raListing->contactInformation;
        $command->institution = $raListing->raInstitution;

        $form = $this->createForm(AmendRegistrationAuthorityInformationType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->notice(sprintf("RA(A) '%s' information amendment form submitted, processing", $identityId));

            if ($this->raService->amendRegistrationAuthorityInformation($command)) {
                $this->addFlash('success', $this->translator->trans('ra.management.amend_ra_info.info_amended'));

                $this->logger->notice(sprintf("RA(A) '%s' information successfully amended", $identityId));
                return $this->redirectToRoute('ra_management_manage');
            }

            $this->logger->notice(sprintf("Information of RA(A) '%s' failed to be amended, informing user", $identityId));
            $this->addFlash('error', 'ra.management.amend_ra_info.error.middleware_command_failed');
        }

        return $this->render('ra_management/amend_ra_information.html.twig', [
            'raListing' => $raListing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param         $identityId
     * @param         $raInstitution
     */
    #[Route(
        path: '/management/retract-registration-authority/{identityId}/{raInstitution}',
        name: 'ra_management_retract_registration_authority',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('ROLE_RAA')]
    public function retractRegistrationAuthority(Request $request, $identityId, $raInstitution): Response
    {
        $this->logger->notice(sprintf("Loading retract registration authority form for RA(A) '%s'", $identityId));

        $raListing = $this->raListingService->get($identityId, $raInstitution, $this->getUser()->getIdentity()->id);
        if (!$raListing) {
            $this
                ->logger
                ->warning(sprintf(
                    "RA listing for identity ID '%s@%s' not found",
                    $identityId,
                    $this->getUser()->getIdentity()->institution,
                ));
            throw new NotFoundHttpException(sprintf("RA listing for identity ID '%s' not found", $identityId));
        }

        $command = new RetractRegistrationAuthorityCommand();
        $command->identityId = $identityId;
        $command->institution = $raListing->raInstitution;

        $form = $this->createForm(RetractRegistrationAuthorityType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('button-group')->get('cancel')->isClicked()) {
                $this->logger->notice('Retraction of registration authority cancelled');
                return $this->redirectToRoute('ra_management_manage');
            }

            $this->logger->notice(sprintf('Confirmed retraction of RA credentials for identity "%s"', $identityId));

            if ($this->raService->retractRegistrationAuthority($command)) {
                $this->logger->notice(sprintf('Registration authority for identity "%s" retracted', $identityId));

                $this->addFlash('success', $this->translator->trans('ra.management.retract_ra.success'));
                return $this->redirectToRoute('ra_management_manage');
            }

            $this->logger->notice(sprintf(
                'Could not retract Registration Authority credentials for identity "%s"',
                $identityId,
            ));
            $this->addFlash('error', 'ra.management.retract_ra.middleware_command_failed');
        }

        return $this->render('ra_management/confirm_retract_ra.html.twig', [
            'raListing' => $raListing,
            'form'      => $form->createView()
        ]);
    }
}
