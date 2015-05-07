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
use Surfnet\StepupRa\RaBundle\Command\CreateRaCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaCandidatesCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RaManagementController extends Controller
{
    public function manageAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);

        $logger = $this->get('logger');
        $institution = $this->getUser()->institution;

        $logger->notice(sprintf('Loading overview of RA(A)s for institution "%s"', $institution));

        $searchQuery = (new RaListingSearchQuery($this->getUser()->institution, 1))
            ->setOrderBy($request->get('orderBy', 'commonName'))
            ->setOrderDirection($request->get('orderDirection', 'asc'));

        $service = $this->getRaService();
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

        return $this->render(
            'SurfnetStepupRaRaBundle:RaManagement:manage.html.twig',
            [
                'raList'     => $raList->getElements(),
                'pagination' => $pagination
            ]
        );
    }

    public function raCandidateSearchAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);

        $logger = $this->get('logger');
        $institution = $this->getUser()->institution;

        $logger->notice(sprintf('Searching for RaCandidates within institution "%s"', $institution));

        $command                 = new SearchRaCandidatesCommand();
        $command->institution    = $institution;
        $command->pageNumber     = $request->get('p', 1);
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

        $identityId = $request->get('identityId');
        $raCandidate = $this->getRaCandidateService()->getRaCandidateByIdentityId($identityId);

        if (!$raCandidate) {
            throw new NotFoundHttpException();
        }

        if ($raCandidate->institution !== $this->getUser()->institution) {
            throw $this->createAccessDeniedException();
        }

        $command              = new CreateRaCommand();
        $command->identityId  = $identityId;
        $command->institution = $this->getUser()->institution;

        $form = $this->createForm('ra_management_create_ra', $command)->handleRequest($request);
        if ($form->isValid()) {
//            $this->getRaCandidateService()->createRa($command)
        }

        return $this->render('SurfnetStepupRaRaBundle:RaManagement:createRa.html.twig', [
            'raCandidate' => $raCandidate,
            'form'        => $form->createView()
        ]);
    }

    /**
     * @return \Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaListingService
     */
    private function getRaService()
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
