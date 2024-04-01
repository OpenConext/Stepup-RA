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

use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaCandidateInstitution;
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
use Surfnet\StepupRa\RaBundle\Service\RaListingService;
use Surfnet\StepupRa\RaBundle\Value\RoleAtInstitution;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RaManagementController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function manageAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RAA');
        $this->denyAccessUnlessGranted('ROLE_SRAA');

        $logger = $this->get('logger');
        $institution = $this->getUser()->institution;
        $logger->notice(sprintf('Loading overview of RA(A)s for institution "%s"', $institution));

        $identity = $this->getCurrentUser();

        $service = $this->getRaListingService();

        $command = new SearchRaListingCommand();
        $command->actorId = $identity->id;
        $command->pageNumber = (int) $request->get('p', 1);
        $command->orderBy = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        // The options that will populate the institution filter choice list.
        $raList = $service->search($command);
        $command->institutionFilterOptions = $raList->getFilterOption('institution');
        $command->raInstitutionFilterOptions = $raList->getFilterOption('raInstitution');

        $form = $this->createForm(SearchRaListingType::class, $command, ['method' => 'get']);
        $form->handleRequest($request);

        $raList = $service->search($command);

        $pagination = $this->getPaginator()->paginate(
            $raList->getTotalItems() > 0 ? $raList->getElements() : [],
            $raList->getCurrentPage(),
            $raList->getItemsPerPage()
        );
        $pagination->setTotalItemCount($raList->getTotalItems());

        $logger->notice(sprintf(
            'Searching for RA(A)s yielded "%d" results',
            $raList->getTotalItems()
        ));

        /** @var \Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListing[] $raListings */
        $raListings = $raList->getElements();

        return $this->render(
            'SurfnetStepupRaRaBundle:ra_management:manage.html.twig',
            [
                'form' => $form->createView(),
                'raList' => $raListings,
                'numberOfResults' => $raList->getTotalItems(),
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function raCandidateSearchAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_RAA');
        $this->denyAccessUnlessGranted('ROLE_SRAA');

        $logger = $this->get('logger');
        $identity = $this->getCurrentUser();
        $institution = $identity->institution;

        $logger->notice(sprintf('Searching for RaCandidates within institution "%s"', $institution));

        $service = $this->getRaCandidateService();

        $command                   = new SearchRaCandidatesCommand();
        $command->actorId          = $identity->id;
        $command->actorInstitution = $institution;
        $command->raInstitution    = null;
        $command->pageNumber       = (int) $request->get('p', 1);
        $command->orderBy          = $request->get('orderBy');
        $command->orderDirection   = $request->get('orderDirection');

        $raCandidateList = $service->search($command);

        // The options that will populate the institution filter choice list.
        $command->institutionFilterOptions = $raCandidateList->getFilterOption('institution');

        $form = $this->createForm(SearchRaCandidatesType::class, $command, ['method' => 'get']);
        $form->handleRequest($request);

        $raCandidateList = $service->search($command);

        $pagination = $this->getPaginator()->paginate(
            $raCandidateList->getTotalItems() > 0 ? $raCandidateList->getElements() : [],
            $raCandidateList->getCurrentPage(),
            $raCandidateList->getItemsPerPage()
        );
        $pagination->setTotalItemCount($raCandidateList->getTotalItems());

        $logger->notice(sprintf(
            'Searching for RaCandidates within institution "%s" yielded "%s" results',
            $institution,
            $raCandidateList->getTotalItems()
        ));

        return $this->render(
            'SurfnetStepupRaRaBundle:ra_management:ra_candidate_overview.html.twig',
            [
                'form'         => $form->createView(),
                'raCandidates' => $raCandidateList,
                'pagination'   => $pagination
            ]
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function createRaAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_RAA');
        $this->denyAccessUnlessGranted('ROLE_SRAA');
        $logger = $this->get('logger');

        $logger->notice('Page for Accreditation of Identity to Ra or Raa requested');
        $identityId = $request->get('identityId');

        $raCandidate = $this->getRaCandidateService()->getRaCandidate($identityId, $this->getUser()->id);

        if (!$raCandidate->raCandidate) {
            $logger->warning(sprintf('RaCandidate based on identity "%s" not found', $identityId));
            throw new NotFoundHttpException();
        }

        $options = array_map(function (RaCandidateInstitution $institution) {
            return $institution->institution;
        }, $raCandidate->institutions->getElements());
        $selectOptions = array_combine($options, $options);

        $command = new AccreditCandidateCommand();
        $command->identityId = $identityId;
        $command->institution = $raCandidate->raCandidate->institution;
        $command->roleAtInstitution = new RoleAtInstitution();
        $command->roleAtInstitution->setInstitution($this->getUser()->institution);
        $command->availableInstitutions = $selectOptions;

        $form = $this->createForm(CreateRaType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $logger->debug('Accreditation form submitted, start processing command');

            $success = $this->getRaCandidateService()->accreditCandidate($command);

            if ($success) {
                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('ra.management.create_ra.identity_accredited')
                );

                $logger->debug('Identity Accredited, redirecting to candidate overview');
                return $this->redirectToRoute('ra_management_ra_candidate_search');
            }

            $logger->debug('Identity Accreditation failed, adding error to form');
            $this->addFlash('error', 'ra.management.create_ra.error.middleware_command_failed');
        }

        return $this->render('SurfnetStepupRaRaBundle:ra_management:create_ra.html.twig', [
            'raCandidate' => $raCandidate->raCandidate,
            'form'        => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param         $identityId
     * @param         $raInstitution
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function amendRaInformationAction(Request $request, $identityId, $raInstitution)
    {
        $this->denyAccessUnlessGranted('ROLE_RAA');
        $this->denyAccessUnlessGranted('ROLE_SRAA');

        $logger = $this->get('logger');
        $logger->notice(sprintf("Loading information amendment form for RA(A) '%s'", $identityId));

        $raListing = $this->getRaListingService()->get($identityId, $raInstitution, $this->getUser()->id);

        if (!$raListing) {
            $logger->warning(sprintf("RA listing for identity ID '%s' not found", $identityId));
            throw new NotFoundHttpException(sprintf("RA listing for identity ID '%s' not found", $identityId));
        }

        $command = new AmendRegistrationAuthorityInformationCommand();
        $command->identityId = $raListing->identityId;
        $command->location = $raListing->location;
        $command->contactInformation = $raListing->contactInformation;
        $command->institution = $raListing->raInstitution;

        $form = $this->createForm(AmendRegistrationAuthorityInformationType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $logger->notice(sprintf("RA(A) '%s' information amendment form submitted, processing", $identityId));

            if ($this->get('ra.service.ra')->amendRegistrationAuthorityInformation($command)) {
                $this->addFlash('success', $this->get('translator')->trans('ra.management.amend_ra_info.info_amended'));

                $logger->notice(sprintf("RA(A) '%s' information successfully amended", $identityId));
                return $this->redirectToRoute('ra_management_manage');
            }

            $logger->notice(sprintf("Information of RA(A) '%s' failed to be amended, informing user", $identityId));
            $this->addFlash('error', 'ra.management.amend_ra_info.error.middleware_command_failed');
        }

        return $this->render('SurfnetStepupRaRaBundle:ra_management:amend_ra_information.html.twig', [
            'raListing' => $raListing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param         $identityId
     * @param         $raInstitution
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function retractRegistrationAuthorityAction(Request $request, $identityId, $raInstitution)
    {
        $this->denyAccessUnlessGranted('ROLE_RAA');
        $this->denyAccessUnlessGranted('ROLE_SRAA');
        $logger = $this->get('logger');

        $logger->notice(sprintf("Loading retract registration authority form for RA(A) '%s'", $identityId));

        $raListing = $this->getRaListingService()->get($identityId, $raInstitution, $this->getUser()->id);
        if (!$raListing) {
            $logger->warning(sprintf("RA listing for identity ID '%s@%s' not found", $identityId, $this->getUser()->institution));
            throw new NotFoundHttpException(sprintf("RA listing for identity ID '%s' not found", $identityId));
        }

        $command = new RetractRegistrationAuthorityCommand();
        $command->identityId = $identityId;
        $command->institution = $raListing->raInstitution;

        $form = $this->createForm(RetractRegistrationAuthorityType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('button-group')->get('cancel')->isClicked()) {
                $logger->notice('Retraction of registration authority cancelled');
                return $this->redirectToRoute('ra_management_manage');
            }

            $logger->notice(sprintf('Confirmed retraction of RA credentials for identity "%s"', $identityId));

            if ($this->get('ra.service.ra')->retractRegistrationAuthority($command)) {
                $logger->notice(sprintf('Registration authority for identity "%s" retracted', $identityId));

                $this->addFlash('success', $this->get('translator')->trans('ra.management.retract_ra.success'));
                return $this->redirectToRoute('ra_management_manage');
            }

            $logger->notice(sprintf(
                'Could not retract Registration Authority credentials for identity "%s"',
                $identityId
            ));
            $this->addFlash('error', 'ra.management.retract_ra.middleware_command_failed');
        }

        return $this->render('SurfnetStepupRaRaBundle:ra_management:confirm_retract_ra.html.twig', [
            'raListing' => $raListing,
            'form'      => $form->createView()
        ]);
    }

    /**
     * @return RaListingService
     */
    private function getRaListingService()
    {
        return $this->get('ra.service.ra_listing');
    }

    /**
     * @return \Surfnet\StepupRa\RaBundle\Service\RaCandidateService
     */
    private function getRaCandidateService()
    {
        return $this->get('ra.service.ra_candidate');
    }

    /**
     * @return \Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity
     */
    private function getCurrentUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }

    /**
     * @return \Knp\Component\Pager\Paginator
     */
    private function getPaginator()
    {
        return $this->get('knp_paginator');
    }
}
