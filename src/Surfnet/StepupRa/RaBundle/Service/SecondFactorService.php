<?php

declare(strict_types = 1);

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
use Surfnet\StepupMiddlewareClient\Exception\StepupMiddlewareClientException;
use Surfnet\StepupMiddlewareClient\Identity\Dto\VerifiedSecondFactorSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\VerifiedSecondFactor;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\SecondFactorService as ApiSecondFactorService;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;

class SecondFactorService
{
    public function __construct(
        private readonly ApiSecondFactorService $apiSecondFactorService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function findVerifiedSecondFactorByRegistrationCode(
        string $registrationCode,
        string $actorId,
    ): ?VerifiedSecondFactor
    {
        $query = new VerifiedSecondFactorSearchQuery();
        $query->setActorId($actorId);
        $query->setRegistrationCode($registrationCode);

        try {
            $result = $this->apiSecondFactorService->searchVerified($query);
        } catch (StepupMiddlewareClientException $e) {
            $message = sprintf('Exception when searching verified second factors: "%s"', $e->getMessage());
            $this->logger->critical($message);
            throw new RuntimeException($message, 0, $e);
        }

        /** @var VerifiedSecondFactor[] $elements */
        $elements = $result->getElements();
        $elementCount = count($elements);

        if ($elementCount === 1) {
            return reset($elements);
        }

        if ($elementCount === 0) {
            return null;
        }

        throw new RuntimeException(
            sprintf('Got an unexpected amount of identities, expected 0 or 1, got "%d"', $elementCount),
        );
    }
}
