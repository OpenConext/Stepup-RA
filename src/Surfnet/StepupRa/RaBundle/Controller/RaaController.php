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

namespace Surfnet\StepupRa\RaBundle\Controller;

use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Service\InstitutionConfigurationOptionsService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RaaController extends Controller
{

    public function institutionConfigurationAction()
    {
        $this->denyAccessUnlessGranted(['ROLE_RAA', 'ROLE_SRAA']);
        $token = $this->get('security.token_storage')->getToken();

        $logger = $this->get('logger');
        /** @var Identity $identity */
        $identity = $token->getUser();
        $institution = $identity->institution;

        $logger->notice(sprintf('Opening the institution configuration for "%s"', $institution));

        $configuration = $this->getInstitutionConfigurationOptionsService()
            ->getInstitutionConfigurationOptionsFor($institution);

        if (!$configuration) {
            $logger->warning(sprintf('Unable to find the institution configuration for "%s"', $institution));
            return $this->createNotFoundException('The institution configuration could not be found');
        }

        return $this->render(
            '@SurfnetStepupRaRa/InstitutionConfiguration/overview.html.twig',
            [
                'configuration' => (array)$configuration,
                'institution' => $institution,
            ]
        );
    }

    /**
     * @return InstitutionConfigurationOptionsService
     */
    private function getInstitutionConfigurationOptionsService()
    {
        return $this->get('ra.service.institution_configuration_options');
    }
}
