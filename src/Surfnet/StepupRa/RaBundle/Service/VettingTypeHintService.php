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

namespace Surfnet\StepupRa\RaBundle\Service;

use Psr\Log\LoggerInterface;
use Surfnet\StepupMiddlewareClient\Identity\Dto\VettingTypeHint;
use Surfnet\StepupMiddlewareClientBundle\Exception\NotFoundException;
use Surfnet\StepupMiddlewareClientBundle\Identity\Command\SaveVettingTypeHintCommand;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\VettingTypeHintService as LibraryVettingTypeHintService;
use Surfnet\StepupRa\RaBundle\Command\VettingTypeHintCommand;

class VettingTypeHintService
{
    public function __construct(
        private readonly CommandService $commandService,
        private readonly LibraryVettingTypeHintService $service,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function save(VettingTypeHintCommand $command)
    {
        $middlewareCommand = new SaveVettingTypeHintCommand();
        $middlewareCommand->identityId = $command->identityId;
        $middlewareCommand->institution = $command->institution;
        $middlewareCommand->hints = $command->hints;

        $result = $this->commandService->execute($middlewareCommand);

        if (!$result->isSuccessful()) {
            $this->logger->critical(sprintf(
                'Saving of the vetting type hint for institution "%s" by user "%s" failed: "%s"',
                $middlewareCommand->institution,
                $command->identityId,
                implode(", ", $result->getErrors()),
            ));
        }

        return $result->isSuccessful();
    }

    public function findBy(string $institution): ?VettingTypeHint
    {
        try {
            return $this->service->findOne($institution);
        } catch (NotFoundException) {
            return null;
        }
    }
}
