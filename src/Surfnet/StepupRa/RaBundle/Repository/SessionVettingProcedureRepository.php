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

namespace Surfnet\StepupRa\RaBundle\Repository;

use Surfnet\StepupRa\RaBundle\VettingProcedure;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionVettingProcedureRepository implements VettingProcedureRepository
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly string $namespace,
    ) {
    }

    public function store(VettingProcedure $vettingProcedure): void
    {
        $this->requestStack->getSession()->set(sprintf('%s:%s', $this->namespace, $vettingProcedure->getId()), $vettingProcedure);
    }

    public function retrieve(string $id): ?VettingProcedure
    {
        return $this->requestStack->getSession()->get(sprintf('%s:%s', $this->namespace, $id));
    }

    public function remove(string $id): mixed
    {
        return $this->requestStack->getSession()->remove(sprintf('%s:%s', $this->namespace, $id));
    }
}
