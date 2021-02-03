<?php

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

use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;
use Surfnet\StepupRa\RaBundle\VettingProcedure;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionVettingProcedureRepository implements VettingProcedureRepository
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $namespace;

    public function __construct(SessionInterface $session, string $namespace)
    {
        if (!is_string($namespace)) {
            throw InvalidArgumentException::invalidType('string', 'namespace', $namespace);
        }

        $this->session = $session;
        $this->namespace = $namespace;
    }

    public function store(VettingProcedure $vettingProcedure)
    {
        $this->session->set(sprintf('%s:%s', $this->namespace, $vettingProcedure->getId()), $vettingProcedure);
    }

    public function retrieve(string $id)
    {
        return $this->session->get(sprintf('%s:%s', $this->namespace, $id));
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function remove(string $id)
    {
        return $this->session->remove(sprintf('%s:%s', $this->namespace, $id));
    }
}
