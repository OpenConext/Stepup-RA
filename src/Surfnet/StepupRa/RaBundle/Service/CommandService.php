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

use Surfnet\StepupMiddlewareClientBundle\Command\Command;
use Surfnet\StepupMiddlewareClientBundle\Command\Metadata;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupMiddlewareClientBundle\Service\CommandService as MiddlewareCommandService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class CommandService
{
    public function __construct(
        private MiddlewareCommandService $commandService,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function execute(Command $command): \Surfnet\StepupMiddlewareClient\Service\ExecutionResult
    {
        /** @var Identity $identity */
        $identity = $this->tokenStorage->getToken()->getUser();

        return $this->commandService->execute($command, new Metadata($identity->id, $identity->institution));
    }
}
