<?php

/**
 * Copyright 2016 SURFnet B.V.
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

use Surfnet\StepupMiddlewareClientBundle\Configuration\Dto\InstitutionConfigurationOptions;
use Surfnet\StepupMiddlewareClientBundle\Configuration\Service\InstitutionConfigurationOptionsService
    as ApiInstitutionConfigurationOptionsService;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;

final class InstitutionConfigurationOptionsService implements InstitutionConfigurationOptionsServiceInterface
{
    /**
     * @var ApiInstitutionConfigurationOptionsService
     */
    private $apiInstitutionConfigurationOptionsService;

    public function __construct(ApiInstitutionConfigurationOptionsService $apiService)
    {
        $this->apiInstitutionConfigurationOptionsService = $apiService;
    }

    /**
     * @param string $institution
     * @return null|InstitutionConfigurationOptions
     */
    public function getInstitutionConfigurationOptionsFor($institution): ?InstitutionConfigurationOptions
    {
        $configuration = $this->apiInstitutionConfigurationOptionsService
            ->getInstitutionConfigurationOptionsFor($institution);

        if (!$configuration) {
            throw new RuntimeException(sprintf('Unable to load the institution configuration for "%s"', $institution));
        }

        /**
         * If the FGA options are null (which they may be) then set them with the default value.
         * This is the own institution.
         */
        if (is_null($configuration->useRa)) {
            $configuration->useRa = [$institution];
        }

        if (is_null($configuration->useRaa)) {
            $configuration->useRaa = [$institution];
        }

        if (is_null($configuration->selectRaa)) {
            $configuration->selectRaa = [$institution];
        }

        return $configuration;
    }
}
