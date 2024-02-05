<?php

/**
 * Copyright 2015 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Controller\Vetting;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class SecondFactorController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    )
    {
    }

    protected function assertSecondFactorEnabled(string $type): void
    {
        if (!in_array($type, $this->getParameter('surfnet_stepup_ra.enabled_second_factors'))) {
            $this->logger->warning('A controller action was called for a disabled second factor');

            throw $this->createNotFoundException();
        }
    }
}
