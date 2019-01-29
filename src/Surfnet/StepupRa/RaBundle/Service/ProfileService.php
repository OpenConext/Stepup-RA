<?php

/**
 * Copyright 2019 SURFnet B.V.
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
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Profile;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\ProfileService as ApiProfileService;

class ProfileService
{
    /**
     * @var ApiProfileService
     */
    private $apiProfileService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ApiProfileService $apiProfileService,
        LoggerInterface $logger
    ) {
        $this->apiProfileService = $apiProfileService;
        $this->logger = $logger;
    }

    /**
     * @param string $identityId
     * @return null|Profile
     */
    public function findByIdentityId($identityId)
    {
        return $this->apiProfileService->get($identityId);
    }
}
