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

use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\StepupRa\RaBundle\Command\ExportRaSecondFactorsCommand;
use Surfnet\StepupRa\RaBundle\Command\RevokeSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaSecondFactorsCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchSecondFactorAuditLogCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\RevokeSecondFactorType;
use Surfnet\StepupRa\RaBundle\Form\Type\SearchRaSecondFactorsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) By making the Form Type classes explicit, MD now realizes couping
 *                                                 is to high.
 */
final class SecondFactorController extends AbstractController
{
    /**
     * @Template
     * @return array|Response
     */
    public function search(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_RA');

        $identity = $this->getCurrentUser();
        $this->get('logger')->notice('Starting search for second factors');

        $command = new SearchRaSecondFactorsCommand();
        $command->actorId = $identity->id;
        $command->pageNumber = (int) $request->get('p', 1);
        $command->orderBy = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        $secondFactors = $this->getSecondFactorService()->search($command);

        // The options that will populate the institution filter choice list.
        $command->institutionFilterOptions = $secondFactors->getFilterOption('institution');

        $form = $this->createForm(SearchRaSecondFactorsType::class, $command, [
            'method' => 'get',
            'enable_export_button' => $this->isGranted('ROLE_RAA'),
        ]);
        $form->handleRequest($request);

        $secondFactors = $this->getSecondFactorService()->search($command);
        $secondFactorCount = $secondFactors->getTotalItems();

        if ($form->isSubmitted() && $form->getClickedButton()->getName() == 'export') {
            $this->get('logger')->notice('Forwarding to export second factors action');
            return $this->forward('SurfnetStepupRaRaBundle:SecondFactor:export', ['command' => $command]);
        }

        $pagination = $this->getPaginator()->paginate(
            $secondFactors->getElements(),
            $secondFactors->getCurrentPage(),
            $secondFactors->getItemsPerPage(),
        );
        $pagination->setTotalItemCount($secondFactors->getTotalItems());

        $revocationForm = $this->createForm(RevokeSecondFactorType::class, new RevokeSecondFactorCommand());

        $this->get('logger')->notice(sprintf(
            'Searching for second factors yielded "%d" results',
            $secondFactors->getTotalItems(),
        ));

        return [
            'form'                  => $form->createView(),
            'revocationForm'        => $revocationForm->createView(),
            'secondFactors'         => $secondFactors,
            'pagination'            => $pagination,
            'numberOfSecondFactors' => $secondFactorCount,
            'orderBy'               => $command->orderBy,
            'orderDirection'        => $command->orderDirection ?: 'asc',
            'inverseOrderDirection' => $command->orderDirection === 'asc' ? 'desc' : 'asc',
        ];
    }

    public function export(SearchRaSecondFactorsCommand $command)
    {
        $this->denyAccessUnlessGranted('ROLE_RAA');

        $this->get('logger')->notice('Starting export of searched second factors');

        $exportCommand = ExportRaSecondFactorsCommand::fromSearchCommand($command);

        return $this->getSecondFactorService()->export($exportCommand);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function revoke(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_RA');

        $logger = $this->get('logger');

        $logger->notice('Received request to revoke Second Factor');

        $command = new RevokeSecondFactorCommand();
        $command->currentUserId = $this->getCurrentUser()->id;

        $form = $this->createForm(RevokeSecondFactorType::class, $command);
        $form->handleRequest($request);

        $logger->info(sprintf(
            'Sending middleware request to revoke Second Factor "%s" belonging to "%s" on behalf of "%s"',
            $command->secondFactorId,
            $command->identityId,
            $command->currentUserId,
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
     * @return Response
     */
    public function auditLog(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_RA');
        $logger = $this->get('logger');

        $identityId = $request->get('identityId');

        $logger->notice(sprintf('Requested AuditLog for SecondFactors of identity "%s"', $identityId));

        $identity = $this->getIdentityService()->findById($identityId);
        if (!$identity) {
            $logger->notice(sprintf(
                'User with Identity "%s" requested non-existent identity "%s"',
                $this->getCurrentUser()->id,
                $identityId,
            ));

            throw new NotFoundHttpException();
        }

        $logger->info(sprintf('Retrieving audit log for Identity "%s"', $identity->id));

        $command                 = new SearchSecondFactorAuditLogCommand();
        $command->identityId     = $identity->id;
        $command->institution    = $identity->institution;
        $command->pageNumber     = (int) $request->get('p', 1);
        $command->orderBy        = $request->get('orderBy', 'recordedOn');
        $command->orderDirection = $request->get('orderDirection', 'desc');

        $auditLog = $this->getAuditLogService()->getAuditlog($command);
        $pagination = $this->getPaginator()->paginate(
            $auditLog->getElements(),
            $auditLog->getCurrentPage(),
            $auditLog->getItemsPerPage(),
        );
        $pagination->setTotalItemCount($auditLog->getTotalItems());

        $logger->notice(sprintf('Audit log yielded "%d" results, rendering page', $auditLog->getTotalItems()));

        return $this->render(
            'second_factor/audit_log.html.twig',
            [
                'pagination' => $pagination,
                'auditLog'   => $auditLog,
                'identity'   => $identity,
            ],
        );
    }

    /**
     * @return \Surfnet\StepupRa\RaBundle\Service\RaSecondFactorService
     */
    private function getSecondFactorService()
    {
        return $this->get('ra.service.ra_second_factor');
    }

    /**
     * @return \Surfnet\StepupRa\RaBundle\Service\IdentityService
     */
    private function getIdentityService()
    {
        return $this->get('ra.service.identity');
    }

    /**
     * @return \Surfnet\StepupRa\RaBundle\Service\AuditLogService
     */
    private function getAuditLogService()
    {
        return $this->get('ra.service.audit_log');
    }

    /**
     * @return \Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity
     */
    private function getCurrentUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }

    /**
     * @return Paginator
     */
    private function getPaginator()
    {
        return $this->get('knp_paginator');
    }
}
