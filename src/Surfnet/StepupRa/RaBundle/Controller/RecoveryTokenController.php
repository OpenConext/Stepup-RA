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
use Surfnet\StepupRa\RaBundle\Command\RevokeSecondFactorCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRecoveryTokensCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\RevokeSecondFactorType;
use Surfnet\StepupRa\RaBundle\Form\Type\SearchRecoveryTokensType;
use Surfnet\StepupRa\RaBundle\Service\RecoveryTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class RecoveryTokenController extends Controller
{
    /**
     * @var RecoveryTokenService
     */
    private $recoveryTokenService;

    /**
     * @var Paginator
     */
    private $paginator;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        RecoveryTokenService $recoveryTokenService,
        Paginator $paginator,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->recoveryTokenService = $recoveryTokenService;
        $this->paginator = $paginator;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    public function searchAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(['ROLE_RA']);

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
            $recoveryTokens->getItemsPerPage()
        );
        $pagination->setTotalItemCount($recoveryTokenCount);

        // TODO, revocation will be handled on the next PR
        $revocationForm = $this->createForm(RevokeSecondFactorType::class, new RevokeSecondFactorCommand());

        $this->logger->notice(sprintf(
            'Searching for recovery tokens yielded "%d" results',
            $recoveryTokenCount
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
            ]
        );
    }

    private function getCurrentUser(): Identity
    {
        /** @var Identity $identity */
        $identity = $this->tokenStorage->getToken()->getUser();
        return $identity;
    }
}
