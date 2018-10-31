<?php

/**
 * Copyright 2018 SURFnet B.V.
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
use Surfnet\StepupMiddlewareClient\Identity\Dto\RaListingSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListingCollection;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaListingService as MiddlewareClientBundleRaListingService;

class RaListingService
{
    /**
     * @var MiddlewareClientBundleRaListingService
     */
    private $raListingService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MiddlewareClientBundleRaListingService $raListingService,
        LoggerInterface $logger
    ) {
        $this->raListingService = $raListingService;
        $this->logger = $logger;
    }

    /**
     * @param $institution
     * @return array
     */
    public function createChoiceListFor($institution)
    {
        $collection = $this->searchBy($institution);

        if ($collection->getTotalItems() === 0) {
            $this->logger->warning('No RAA institutions found for identity, unable to build the choice list for the RAA switcher');
            return [];
        }

        $choices = [];
        foreach ($collection->getElements() as $item) {
            $choices[$item->raInstitution] = $item->raInstitution;
        }

        // Sort the list alphabetically while preserving the keys
        asort($choices);
        return $choices;
    }

    /**
     * @param string $institution
     * @return RaListingCollection
     */
    public function searchBy($institution)
    {
        $query = new RaListingSearchQuery($institution, 1);
        //$query->setIdentityId($institution);
        return $this->raListingService->search($query);
    }
}
