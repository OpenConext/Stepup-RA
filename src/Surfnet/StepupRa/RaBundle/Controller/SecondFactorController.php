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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\StepupRa\RaBundle\Command\RevokeSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaSecondFactorsCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecondFactorController extends Controller
{
    /**
     * @Template
     * @param Request $request
     * @return array|Response
     */
    public function searchAction(Request $request)
    {
        $identity = $this->getIdentity();
        $this->get('logger')->notice('Starting search for second factors');

        $command = new SearchRaSecondFactorsCommand();
        $command->institution = $identity->institution;
        $command->pageNumber = (int) $request->get('p', 1);
        $command->orderBy = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        $form = $this->createForm('ra_search_ra_second_factors', $command, ['method' => 'get']);
        $form->handleRequest($request);

        $secondFactors = $this->getSecondFactorService()->search($command);

        $pagination = $this->get('knp_paginator')->paginate(
            $secondFactors->getTotalItems() > 0 ? array_fill(0, $secondFactors->getTotalItems(), 1) : [],
            $secondFactors->getCurrentPage(),
            $secondFactors->getItemsPerPage()
        );

        $revocationForm = $this->createForm('ra_revoke_second_factor', new RevokeSecondFactorCommand());

        $this->get('logger')->notice(sprintf(
            'Searching for second factors yielded "%d" results',
            $secondFactors->getTotalItems()
        ));

        return [
            'form'                  => $form->createView(),
            'revocationForm'        => $revocationForm->createView(),
            'secondFactors'         => $secondFactors,
            'pagination'            => $pagination,
            'orderBy'               => $command->orderBy,
            'orderDirection'        => $command->orderDirection ?: 'asc',
            'inverseOrderDirection' => $command->orderDirection === 'asc' ? 'desc' : 'asc',
        ];
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function revokeAction(Request $request)
    {
        $logger = $this->get('logger');

        $logger->notice('Received request to revoke Second Factor');

        $command = new RevokeSecondFactorCommand();
        $command->currentUserId = $this->getIdentity()->id;

        $form = $this->createForm('ra_revoke_second_factor', $command);
        $form->handleRequest($request);

        $logger->info(sprintf(
            'Sending middleware request to revoke Second Factor "%s" belonging to "%s" on behalf of "%s"',
            $command->secondFactorId,
            $command->identityId,
            $command->currentUserId
        ));

        $translator = $this->get('translator');
        $flashBag = $this->get('session')->getFlashBag();
        if ($this->getSecondFactorService()->revoke($command)) {
            $logger->notice('Second Factor revocation Succeeded');
            $flashBag->add('success', $translator->trans('ra.second_factor.revocation.revoked'));
        } else {
            $logger->notice('Second Factor revocation Failed');
            $flashBag->add('error', $translator->trans('ra.second_factor.revocation.could_not_revoke'));
        }

        $logger->notice('Redirecting back to Second Factor Search Page');

        return $this->redirectToRoute('ra_second_factors_search');
    }

    /**
     * @return \Surfnet\StepupRa\RaBundle\Service\RaSecondFactorService
     */
    private function getSecondFactorService()
    {
        return $this->get('ra.service.ra_second_factor');
    }

    /**
     * @return \Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity
     */
    private function getIdentity()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }
}
