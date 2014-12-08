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

namespace Surfnet\StepupRa\RaBundle\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Surfnet\StepupRa\RaBundle\Repository\VettingProcedureRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VettingProcedureParamConverter implements ParamConverterInterface
{
    /**
     * @var VettingProcedureRepository
     */
    private $repository;

    /**
     * @param VettingProcedureRepository $repository
     */
    public function __construct(VettingProcedureRepository $repository)
    {
        $this->repository = $repository;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $uuid = $request->attributes->get('procedureUuid');
        $procedure = $this->repository->retrieve($uuid);

        if ($procedure === null) {
            throw new NotFoundHttpException(sprintf("No vetting procedure with UUID '%s' is active.", $uuid));
        }

        $request->attributes->set($configuration->getName(), $procedure);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === 'Surfnet\StepupRa\RaBundle\VettingProcedure';
    }
}
