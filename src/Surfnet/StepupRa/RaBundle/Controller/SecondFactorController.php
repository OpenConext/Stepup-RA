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

use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Command\ExportRaSecondFactorsCommand;
use Surfnet\StepupRa\RaBundle\Command\RevokeSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaSecondFactorsCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchSecondFactorAuditLogCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\RevokeSecondFactorType;
use Surfnet\StepupRa\RaBundle\Form\Type\SearchRaSecondFactorsType;
use Surfnet\StepupRa\RaBundle\Service\AuditLogService;
use Surfnet\StepupRa\RaBundle\Service\IdentityService;
use Surfnet\StepupRa\RaBundle\Service\RaSecondFactorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) By making the Form Type classes explicit, MD now realizes couping
 *                                                 is to high.
 */
final class SecondFactorController extends AbstractController
{
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly LoggerInterface $logger,
        private readonly RaSecondFactorService $secondFactorService,
        private readonly UserProviderInterface $identityService,
        private readonly AuditLogService $auditLogService,
    )
    {
    }

    public function search(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RA');

        $identity = $this->getCurrentUser();
        $this->logger->notice('Starting search for second factors');

        $command = new SearchRaSecondFactorsCommand();
        $command->actorId = $identity->id;
        $command->pageNumber = (int) $request->get('p', 1);
        $command->orderBy = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        $secondFactors = $this->secondFactorService->search($command);

        // The options that will populate the institution filter choice list.
        $command->institutionFilterOptions = $secondFactors->getFilterOption('institution');

        $form = $this->createForm(SearchRaSecondFactorsType::class, $command, [
            'method' => 'get',
            'enable_export_button' => $this->isGranted('ROLE_RAA'),
        ]);
        $form->handleRequest($request);

        $secondFactors = $this->secondFactorService->search($command);
        $secondFactorCount = $secondFactors->getTotalItems();

        if ($form->isSubmitted() && $form->getClickedButton()->getName() == 'export') {
            $this->logger->notice('Forwarding to export second factors action');
            return $this->forward('\Surfnet\StepupRa\RaBundle\Controller\SecondFactorController::export', ['command' => $command]);
        }

        $pagination = $this->paginator->paginate(
            $secondFactors->getElements(),
            $secondFactors->getCurrentPage(),
            $secondFactors->getItemsPerPage(),
        );
        $pagination->setTotalItemCount($secondFactors->getTotalItems());

        $revocationForm = $this->createForm(RevokeSecondFactorType::class, new RevokeSecondFactorCommand());

        $this->logger->notice(sprintf(
            'Searching for second factors yielded "%d" results',
            $secondFactors->getTotalItems(),
        ));

        return $this->render('second_factor/search.html.twig', [
            'form'                  => $form->createView(),
            'revocationForm'        => $revocationForm->createView(),
            'secondFactors'         => $secondFactors,
            'pagination'            => $pagination,
            'numberOfSecondFactors' => $secondFactorCount,
            'orderBy'               => $command->orderBy,
            'orderDirection'        => $command->orderDirection ?: 'asc',
            'inverseOrderDirection' => $command->orderDirection === 'asc' ? 'desc' : 'asc',
        ]);
    }

    public function export(SearchRaSecondFactorsCommand $command): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RAA');

        $this->logger->notice('Starting export of searched second factors');

        $exportCommand = ExportRaSecondFactorsCommand::fromSearchCommand($command);

        return $this->secondFactorService->export($exportCommand);
    }

    public function revoke(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_RA');

        $this->logger->notice('Received request to revoke Second Factor');

        $command = new RevokeSecondFactorCommand();
        $command->currentUserId = $this->getCurrentUser()->id;

        $form = $this->createForm(RevokeSecondFactorType::class, $command);
        $form->handleRequest($request);

        $this->logger->info(sprintf(
            'Sending middleware request to revoke Second Factor "%s" belonging to "%s" on behalf of "%s"',
            $command->secondFactorId,
            $command->identityId,
            $command->currentUserId,
        ));

        $translator = $this->container->get('translator');
        $flashBag = $this->container->get('session')->getFlashBag();
        if ($this->secondFactorService->revoke($command)) {
            $this->logger->notice('Second Factor revocation Succeeded');
            $flashBag->add('success', $translator->trans('ra.second_factor.revocation.revoked'));
        } else {
            $this->logger->notice('Second Factor revocation Failed');
            $flashBag->add('error', $translator->trans('ra.second_factor.revocation.could_not_revoke'));
        }

        $this->logger->notice('Redirecting back to Second Factor Search Page');

        return $this->redirectToRoute('ra_second_factors_search');
    }

    public function auditLog(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RA');

        $identityId = $request->get('identityId');

        $this->logger->notice(sprintf('Requested AuditLog for SecondFactors of identity "%s"', $identityId));

        $identity = $this->identityService->findById($identityId);
        if (!$identity) {
            $this->logger->notice(sprintf(
                'User with Identity "%s" requested non-existent identity "%s"',
                $this->getCurrentUser()->id,
                $identityId,
            ));

            throw new NotFoundHttpException();
        }

        $this->logger->info(sprintf('Retrieving audit log for Identity "%s"', $identity->id));

        $command                 = new SearchSecondFactorAuditLogCommand();
        $command->identityId     = $identity->id;
        $command->institution    = $identity->institution;
        $command->pageNumber     = (int) $request->get('p', 1);
        $command->orderBy        = $request->get('orderBy', 'recordedOn');
        $command->orderDirection = $request->get('orderDirection', 'desc');

        $auditLog = $this->auditLogService->getAuditlog($command);
        $pagination = $this->paginator->paginate(
            $auditLog->getElements(),
            $auditLog->getCurrentPage(),
            $auditLog->getItemsPerPage(),
        );
        $pagination->setTotalItemCount($auditLog->getTotalItems());

        $this->logger->notice(sprintf('Audit log yielded "%d" results, rendering page', $auditLog->getTotalItems()));

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
     * @return Identity
     */
    private function getCurrentUser(): Identity
    {
        return $this->container->get('security.token_storage')->getToken()->getUser();
    }

}
