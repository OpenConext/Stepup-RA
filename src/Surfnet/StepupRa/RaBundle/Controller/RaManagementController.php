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

use Surfnet\StepupMiddlewareClient\Identity\Dto\RaListingSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaCandidateInstitution;
use Surfnet\StepupRa\RaBundle\Command\AccreditCandidateCommand;
use Surfnet\StepupRa\RaBundle\Command\AmendRegistrationAuthorityInformationCommand;
use Surfnet\StepupRa\RaBundle\Command\RetractRegistrationAuthorityCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaCandidatesCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\AmendRegistrationAuthorityInformationType;
use Surfnet\StepupRa\RaBundle\Form\Type\CreateRaType;
use Surfnet\StepupRa\RaBundle\Form\Type\RetractRegistrationAuthorityType;
use Surfnet\StepupRa\RaBundle\Form\Type\SearchRaCandidatesType;
use Surfnet\StepupRa\RaBundle\Service\InstitutionConfigurationOptionsService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RaManagementController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);

        $logger = $this->get('logger');
        $institution = $this->getUser()->institution;
        $logger->notice(sprintf('Loading overview of RA(A)s for institution "%s"', $institution));

        $searchQuery = (new RaListingSearchQuery($institution, 1))
            ->setOrderBy($request->get('orderBy', 'commonName'))
            ->setOrderDirection($request->get('orderDirection', 'asc'));

        $service = $this->getRaListingService();
        $raList = $service->search($searchQuery);

        $pagination = $this->getPaginator()->paginate(
            $raList->getTotalItems() > 0 ? array_fill(0, $raList->getTotalItems(), 1) : [],
            $raList->getCurrentPage(),
            $raList->getItemsPerPage()
        );

        $logger->notice(sprintf(
            'Created overview of "%d" RA(A)s for institution "%s"',
            $raList->getTotalItems(),
            $institution
        ));

        /** @var \Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListing[] $raListings */
        $raListings = $raList->getElements();

        return $this->render(
            'SurfnetStepupRaRaBundle:RaManagement:manage.html.twig',
            [
                'raList'     => $raListings,
                'pagination' => $pagination
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function raCandidateSearchAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);

        $logger = $this->get('logger');
        $institution = $this->getUser()->institution;

        $logger->notice(sprintf('Searching for RaCandidates within institution "%s"', $institution));

        $command                   = new SearchRaCandidatesCommand();
        $command->actorInstitution = $institution;
        $command->pageNumber       = (int) $request->get('p', 1);
        $command->orderBy          = $request->get('orderBy');
        $command->orderDirection   = $request->get('orderDirection');

        $form = $this->createForm(SearchRaCandidatesType::class, $command, ['method' => 'get']);
        $form->handleRequest($request);

        $service = $this->getRaCandidateService();
        $raCandidateList = $service->search($command);

        $pagination = $this->getPaginator()->paginate(
            $raCandidateList->getTotalItems() > 0 ? array_fill(4, $raCandidateList->getTotalItems(), 1) : [],
            $raCandidateList->getCurrentPage(),
            $raCandidateList->getItemsPerPage()
        );

        $logger->notice(sprintf(
            'Searching for RaCandidates within institution "%s" yielded "%s" results',
            $institution,
            $raCandidateList->getTotalItems()
        ));

        return $this->render(
            'SurfnetStepupRaRaBundle:RaManagement:raCandidateOverview.html.twig',
            [
                'form'         => $form->createView(),
                'raCandidates' => $raCandidateList,
                'pagination'   => $pagination
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createRaAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);
        $logger = $this->get('logger');

        $logger->notice('Page for Accreditation of Identity to Ra or Raa requested');
        $identityId = $request->get('identityId');

        $raCandidate = $this->getRaCandidateService()->getRaCandidate($identityId, $this->getUser()->institution);

        if (!$raCandidate) {
            $logger->warning(sprintf('RaCandidate based on identity "%s" not found', $identityId));
            throw new NotFoundHttpException();
        }

        $options = array_map(function (RaCandidateInstitution $institution) {
            return $institution->institution;
        }, $raCandidate->institutions->getElements());
        $selectOptions = array_combine($options, $options);

        $command                   = new AccreditCandidateCommand();
        $command->identityId       = $identityId;
        $command->institution      = $raCandidate->raCandidate->institution;
        $command->raInstitution    = $this->getUser()->institution;
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

        return $this->render('SurfnetStepupRaRaBundle:RaManagement:createRa.html.twig', [
            'raCandidate' => $raCandidate->raCandidate,
            'form'        => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param         $identityId
     * @param         $raInstitution
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function amendRaInformationAction(Request $request, $identityId, $raInstitution)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);

        $logger = $this->get('logger');
        $logger->notice(sprintf("Loading information amendment form for RA(A) '%s'", $identityId));

        $raListing = $this->getRaListingService()->get($identityId, $raInstitution);

        if (!$raListing) {
            $logger->warning(sprintf("RA listing for identity ID '%s' not found", $identityId));
            throw new NotFoundHttpException(sprintf("RA listing for identity ID '%s' not found", $identityId));
        }

        $command = new AmendRegistrationAuthorityInformationCommand();
        $command->identityId = $raListing->identityId;
        $command->location = $this->getUser()->institution;
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

        return $this->render('SurfnetStepupRaRaBundle:RaManagement:amendRaInformation.html.twig', [
            'raListing' => $raListing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param         $identityId
     * @param         $raInstitution
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function retractRegistrationAuthorityAction(Request $request, $identityId, $raInstitution)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);
        $logger = $this->get('logger');

        $logger->notice(sprintf("Loading retract registration authority form for RA(A) '%s'", $identityId));

        $raListing = $this->getRaListingService()->get($identityId, $raInstitution);
        if (!$raListing) {
            $logger->warning(sprintf("RA listing for identity ID '%s@%s' not found' not found", $identityId, $this->getUser()->institution));
            throw new NotFoundHttpException(sprintf("RA listing for identity ID '%s' not found", $identityId));
        }

        $command = new RetractRegistrationAuthorityCommand();
        $command->identityId = $identityId;
        $command->institution = $raListing->raInstitution;

        $form = $this->createForm(RetractRegistrationAuthorityType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('cancel')->isClicked()) {
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

        return $this->render('SurfnetStepupRaRaBundle:RaManagement:confirmRetractRa.html.twig', [
            'raListing' => $raListing,
            'form'      => $form->createView()
        ]);
    }

    /**
     * @return \Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaListingService
     */
    private function getRaListingService()
    {
        return $this->get('surfnet_stepup_middleware_client.identity.service.ra_listing');
    }

    /**
     * @return \Surfnet\StepupRa\RaBundle\Service\RaCandidateService
     */
    private function getRaCandidateService()
    {
        return $this->get('ra.service.ra_candidate');
    }

    /**
     * @return \Knp\Component\Pager\Paginator
     */
    private function getPaginator()
    {
        return $this->get('knp_paginator');
    }
}
