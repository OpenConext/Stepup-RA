<?php

/**
 * Copyright 2024 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Controller\Vetting\Gssf;

use Surfnet\SamlBundle\Http\XMLResponse;
use Surfnet\SamlBundle\Metadata\MetadataFactory;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorAssertionService;
use Surfnet\StepupRa\SamlStepupProviderBundle\Provider\ProviderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Orchestrates verification of GSSFs (Generic SAML Second Factors) through GSSPs (Generic SAML Stepup Providers).
 */
final class GssfMetadataController extends AbstractController
{
    public function __construct(
        private readonly ProviderRepository   $providerRepository,
        private readonly SecondFactorAssertionService $secondFactorAssertionService,
    ) {
    }

    #[Route(
        path: '/vetting-procedure/gssf/{provider}/metadata',
        name: 'ra_vetting_gssf_metadata',
        methods: ['GET'],
    )]
    public function __invoke(string $providerName): XMLResponse
    {
        $this->secondFactorAssertionService->assertSecondFactorEnabled($providerName);

        $provider = $this->providerRepository->get($providerName);

        /** @var MetadataFactory $factory */
        $factory = $this->container->get('gssp.provider.' . $provider->getName() . '.metadata.factory');

        return new XMLResponse($factory->generate());
    }

}
