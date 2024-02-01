<?php

/**
 * Copyright 2022 SURFnet bv
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
use Psr\Log\LoggerInterface;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Command\RevokeRecoveryTokenCommand;
use Surfnet\StepupRa\RaBundle\Command\RevokeSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRecoveryTokensCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\RevokeRecoveryTokenType;
use Surfnet\StepupRa\RaBundle\Form\Type\RevokeSecondFactorType;
use Surfnet\StepupRa\RaBundle\Form\Type\SearchRecoveryTokensType;
use Surfnet\StepupRa\RaBundle\Service\RecoveryTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class RecoveryTokenController extends AbstractController
{
    public function __construct(
        private readonly RecoveryTokenService $recoveryTokenService,
        private readonly Paginator $paginator,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function searchAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RA');

        $identity = $this->getCurrentUser();
        $this->get('logger')->notice('Starting search for recovery tokens');

        $command = new SearchRecoveryTokensCommand();
        $command->actorId = $identity->id;
        $command->pageNumber = (int) $request->get('p', 1);
        $command->orderBy = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        // Huh, why do we load the recovery tokens at this point?
        // This is just to get the filter options for the available institutions of the search results.
        $recoveryTokens = $this->recoveryTokenService->search($command);
        $command->institutionFilterOptions = $recoveryTokens->getFilterOption('institution');

        $form = $this->createForm(SearchRecoveryTokensType::class, $command, [
            'method' => 'get',
        ]);
        $form->handleRequest($request);

        $recoveryTokens = $this->recoveryTokenService->search($command);
        $recoveryTokenCount = $recoveryTokens->getTotalItems();
        $pagination = $this->paginator->paginate(
            $recoveryTokens->getElements(),
            $recoveryTokens->getCurrentPage(),
            $recoveryTokens->getItemsPerPage(),
        );
        $pagination->setTotalItemCount($recoveryTokenCount);

        $revocationForm = $this->createForm(RevokeRecoveryTokenType::class, new RevokeRecoveryTokenCommand());

        $this->logger->notice(sprintf(
            'Searching for recovery tokens yielded "%d" results',
            $recoveryTokenCount,
        ));

        return $this->render(
            '@SurfnetStepupRaRa/recovery_token/search.html.twig',
            [
                'form' => $form->createView(),
                'revocationForm' => $revocationForm->createView(),
                'recoveryTokens' => $recoveryTokens,
                'pagination' => $pagination,
                'numberOfRecoveryTokens' => $recoveryTokenCount,
                'orderBy' => $command->orderBy,
                'orderDirection' => $command->orderDirection ?: 'asc',
                'inverseOrderDirection' => $command->orderDirection === 'asc' ? 'desc' : 'asc',
            ],
        );
    }

    public function revokeAction(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_RA');

        $this->logger->notice('Received request to revoke recovery token');

        $command = new RevokeRecoveryTokenCommand();
        $command->currentUserId = $this->getCurrentUser()->id;

        $form = $this->createForm(RevokeRecoveryTokenType::class, $command);
        $form->handleRequest($request);

        $this->logger->info(sprintf(
            'Sending middleware request to revoke recovery token "%s" belonging to "%s" on behalf of "%s"',
            $command->recoveryTokenId,
            $command->identityId,
            $command->currentUserId,
        ));

        if ($this->recoveryTokenService->revoke($command)) {
            $this->logger->notice('Recovery token revocation succeeded');
            $this->addFlash('success', 'ra.recovery_token.revocation.revoked');
        } else {
            $this->logger->notice('Recovery token revocation failed');
            $this->addFlash('error', 'ra.recovery_token.revocation.could_not_revoke');
        }

        $this->logger->notice('Redirecting back to recovery tokens search overview');

        return $this->redirectToRoute('ra_recovery_tokens_search');
    }

    private function getCurrentUser(): Identity
    {
        /** @var Identity $identity */
        $identity = $this->tokenStorage->getToken()->getUser();
        return $identity;
    }
}
