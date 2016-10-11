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

use Assert\Assertion;
use Surfnet\StepupMiddlewareClient\Identity\Dto\RaListingSearchQuery;
use Surfnet\StepupRa\RaBundle\Command\AccreditCandidateCommand;
use Surfnet\StepupRa\RaBundle\Command\AmendRegistrationAuthorityInformationCommand;
use Surfnet\StepupRa\RaBundle\Command\ChangeRaRoleCommand;
use Surfnet\StepupRa\RaBundle\Command\RetractRegistrationAuthorityCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaCandidatesCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
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

        $searchQuery = (new RaListingSearchQuery($this->getUser()->institution, 1))
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

        $pageNumber = $request->get('p', 1);
        Assertion::digit($pageNumber, 'Expected page number to be an integer, got "%s"');

        $command                 = new SearchRaCandidatesCommand();
        $command->institution    = $institution;
        $command->pageNumber     = $pageNumber;
        $command->orderBy        = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        $form = $this->createForm('ra_search_ra_candidates', $command, ['method' => 'get']);
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
        $raCandidate = $this->getRaCandidateService()->getRaCandidateByIdentityId($identityId);

        if (!$raCandidate) {
            $logger->warning(sprintf('RaCandidate based on identity "%s" not found', $identityId));
            throw new NotFoundHttpException();
        }

        if ($raCandidate->institution !== $this->getUser()->institution) {
            $user = $this->getUser();
            $logger->warning(sprintf(
                'Identity "%s" of "%s" illegally tried to accredit tried to accredit Identity "%s" of "%s"',
                $user->id,
                $user->institution,
                $raCandidate->identityId,
                $raCandidate->institution
            ));
            throw $this->createAccessDeniedException();
        }

        $command              = new AccreditCandidateCommand();
        $command->identityId  = $identityId;
        $command->institution = $raCandidate->institution;

        $form = $this->createForm('ra_management_create_ra', $command)->handleRequest($request);
        if ($form->isValid()) {
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
            $form->addError(new FormError('ra.management.create_ra.error.middleware_command_failed'));
        }

        return $this->render('SurfnetStepupRaRaBundle:RaManagement:createRa.html.twig', [
            'raCandidate' => $raCandidate,
            'form'        => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param         $identityId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function amendRaInformationAction(Request $request, $identityId)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);

        $logger = $this->get('logger');
        $logger->notice(sprintf("Loading information amendment form for RA(A) '%s'", $identityId));

        $raListing = $this->getRaListingService()->get($identityId);

        if (!$raListing) {
            $logger->warning(sprintf("RA listing for identity ID '%s' not found", $identityId));
            throw new NotFoundHttpException(sprintf("RA listing for identity ID '%s' not found", $identityId));
        }

        $command = new AmendRegistrationAuthorityInformationCommand();
        $command->identityId = $raListing->identityId;
        $command->location = $raListing->location;
        $command->contactInformation = $raListing->contactInformation;

        $form = $this->createForm('ra_management_amend_ra_info', $command)->handleRequest($request);
        if ($form->isValid()) {
            $logger->notice(sprintf("RA(A) '%s' information amendment form submitted, processing", $identityId));

            if ($this->get('ra.service.ra')->amendRegistrationAuthorityInformation($command)) {
                $this->addFlash('success', $this->get('translator')->trans('ra.management.amend_ra_info.info_amended'));

                $logger->notice(sprintf("RA(A) '%s' information successfully amended", $identityId));
                return $this->redirectToRoute('ra_management_manage');
            }

            $logger->notice(sprintf("Information of RA(A) '%s' failed to be amended, informing user", $identityId));
            $form->addError(new FormError('ra.management.amend_ra_info.error.middleware_command_failed'));
        }

        return $this->render('SurfnetStepupRaRaBundle:RaManagement:amendRaInformation.html.twig', [
            'raListing' => $raListing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param         $identityId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function changeRaRoleAction(Request $request, $identityId)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);
        $logger = $this->get('logger');

        $logger->notice(sprintf("Loading change Ra Role form for RA(A) '%s'", $identityId));

        $raListing = $this->getRaListingService()->get($identityId);
        if (!$raListing) {
            $logger->warning(sprintf("RA listing for identity ID '%s' not found", $identityId));
            throw new NotFoundHttpException(sprintf("RA listing for identity ID '%s' not found", $identityId));
        }

        $command              = new ChangeRaRoleCommand();
        $command->identityId  = $raListing->identityId;
        $command->institution = $raListing->institution;
        $command->role        = $raListing->role;

        $form = $this->createForm('ra_management_change_ra_role', $command)->handleRequest($request);
        if ($form->isValid()) {
            $logger->notice(sprintf('RA(A) "%s" Change Role form submitted, processing', $identityId));

            if ($this->get('ra.service.ra')->changeRegistrationAuthorityRole($command)) {
                $logger->notice('Role successfully changed');

                $this->addFlash('success', $this->get('translator')->trans('ra.management.change_ra_role_changed'));
                return $this->redirectToRoute('ra_management_manage');
            }

            $logger->notice(sprintf('Role of RA(A) "%s" could not be changed, informing user', $identityId));
            $form->addError(new FormError('ra.management.change_ra_role.middleware_command_failed'));
        }

        return $this->render('SurfnetStepupRaRaBundle:RaManagement:changeRaRole.html.twig', [
            'raListing' => $raListing,
            'form'      => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param         $identityId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function retractRegistrationAuthorityAction(Request $request, $identityId)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);
        $logger = $this->get('logger');

        $logger->notice(sprintf("Loading retract registration authority form for RA(A) '%s'", $identityId));

        $raListing = $this->getRaListingService()->get($identityId);
        if (!$raListing) {
            $logger->warning(sprintf("RA listing for identity ID '%s' not found", $identityId));
            throw new NotFoundHttpException(sprintf("RA listing for identity ID '%s' not found", $identityId));
        }

        $command = new RetractRegistrationAuthorityCommand();
        $command->identityId = $identityId;

        $form = $this->createForm('ra_management_retract_registration_authority', $command)->handleRequest($request);
        if ($form->isValid()) {
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
            $form->addError(new FormError('ra.management.retract_ra.middleware_command_failed'));
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
