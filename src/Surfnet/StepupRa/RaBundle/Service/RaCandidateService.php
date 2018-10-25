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

namespace Surfnet\StepupRa\RaBundle\Service;

use Psr\Log\LoggerInterface;
use Surfnet\StepupBundle\Service\SecondFactorTypeService;
use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupBundle\Value\SecondFactorType;
use Surfnet\StepupMiddlewareClient\Identity\Dto\RaCandidateSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Command\AccreditIdentityCommand;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaCandidateService as ApiRaCandidateService;
use Surfnet\StepupRa\RaBundle\Command\AccreditCandidateCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaCandidatesCommand;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RaCandidateService
{
    /**
     * @var \Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaCandidateService
     */
    private $apiRaCandidateService;

    /**
     * @var \Surfnet\StepupRa\RaBundle\Service\CommandService
     */
    private $commandService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var SecondFactorTypeService
     */
    private $secondFactorTypeService;

    public function __construct(
        ApiRaCandidateService $raCandidateService,
        CommandService $commandService,
        LoggerInterface $logger,
        SecondFactorTypeService $secondFactorTypeService
    ) {
        $this->apiRaCandidateService = $raCandidateService;
        $this->commandService = $commandService;
        $this->logger = $logger;
        $this->secondFactorTypeService = $secondFactorTypeService;
    }

    /**
     * @param SearchRaCandidatesCommand $command
     * @return \Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaCandidateCollection
     */
    public function search(SearchRaCandidatesCommand $command)
    {
        $query = new RaCandidateSearchQuery($command->actorInstitution, $command->pageNumber);

        if ($command->name) {
            $query->setCommonName($command->name);
        }

        if ($command->email) {
            $query->setEmail($command->email);
        }

        if ($command->orderBy) {
            $query->setOrderBy($command->orderBy);
        }

        if ($command->institution) {
            $query->setInstitution($command->institution);
        }

        if ($command->orderDirection) {
            $query->setOrderDirection($command->orderDirection);
        }

        $query->setSecondFactorTypes($this->getLoa3SecondFactorTypes());

        return $this->apiRaCandidateService->search($query);
    }

    /**
     * @param string $identityId
     * @param string $institution
     * @return null|\Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaCandidate
     */
    public function getRaCandidate($identityId, $institution)
    {
        if (!is_string($identityId)) {
            throw InvalidArgumentException::invalidType('string', 'identityId', $identityId);
        }

        return $this->apiRaCandidateService->get($identityId, $institution);
    }

    public function accreditCandidate(AccreditCandidateCommand $command)
    {
        $apiCommand                     = new AccreditIdentityCommand();
        $apiCommand->actorInstitution   = $command->actorInstitution;
        $apiCommand->identityId         = $command->identityId;
        $apiCommand->institution        = $command->institution;
        $apiCommand->role               = $command->role;
        $apiCommand->location           = $command->location ?: '';
        $apiCommand->contactInformation = $command->contactInformation ?: '';

        $result = $this->commandService->execute($apiCommand);

        if (!$result->isSuccessful()) {
            $this->logger->critical(
                sprintf(
                    'Accreditation of Identity "%s" of Institution "%s" with role "%s" failed: "%s"',
                    $apiCommand->identityId,
                    $apiCommand->institution,
                    $apiCommand->role,
                    implode(", ", $result->getErrors())
                )
            );
        }

        return $result->isSuccessful();
    }

    /**
     * @return string[]
     */
    private function getLoa3SecondFactorTypes()
    {
        $loa3 = new Loa(Loa::LOA_3, 'LOA3');
        return array_filter(
            $this->secondFactorTypeService->getAvailableSecondFactorTypes(),
            function ($secondFactorType) use ($loa3) {
                $secondFactorType = new SecondFactorType($secondFactorType);
                return $this->secondFactorTypeService->canSatisfy($secondFactorType, $loa3);
            }
        );
    }
}
