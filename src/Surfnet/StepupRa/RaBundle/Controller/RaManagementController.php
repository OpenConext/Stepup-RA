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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RaManagementController extends Controller
{
    public function manageAction()
    {
        $authenticator = $this->get('security.authorization_checker');
        if (!($authenticator->isGranted('ROLE_RAA') || $authenticator->isGranted('ROLE_SRAA'))) {
            $this->createAccessDeniedException(); //throw?
        }

        $searchQuery = (new RaListingSearchQuery($this->getUser()->institution, 1))
            ->setOrderBy('commonName')
            ->setOrderDirection('asc');

        $service = $this->getRaService();
        $raList = $service->search($searchQuery);

        $pagination = $this->get('knp_paginator')->paginate(
            $raList->getTotalItems() > 0 ? array_fill(0, $raList->getTotalItems(), 1) : [],
            $raList->getCurrentPage(),
            $raList->getItemsPerPage()
        );

        return $this->render(
            'SurfnetStepupRaRaBundle:RaManagement:manage.html.twig',
            [
                'raList'     => $raList,
                'pagination' => $pagination
            ]
        );
    }

    /**
     * @return \Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaListingService
     */
    private function getRaService()
    {
        return $this->get('surfnet_stepup_middleware_client.identity.service.ra_listing');
    }
}
