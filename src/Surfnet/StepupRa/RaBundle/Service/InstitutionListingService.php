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

use Surfnet\StepupMiddlewareClientBundle\Identity\Service\InstitutionListingService as ApiInstitutionListingService;

class InstitutionListingService
{
    /**
     * @var ApiInstitutionListingService
     */
    private $institutionListingService;

    /**
     * @param ApiInstitutionListingService $institutionListingService
     */
    public function __construct(
        ApiInstitutionListingService $institutionListingService
    ) {
        $this->institutionListingService = $institutionListingService;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $collection = $this->institutionListingService->getAll();
        $listings = $collection->getElements();

        $options = [];
        foreach ($listings as $listing) {
            $result[$listing->institution] = $listing->institution;
        }

        return $options;
    }
}
