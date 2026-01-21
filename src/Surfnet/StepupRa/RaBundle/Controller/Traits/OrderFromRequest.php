<?php

/**
 * Copyright 2026 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Controller\Traits;

use Symfony\Component\HttpFoundation\Request;

trait OrderFromRequest
{

    /**
     * Convenience method to read orderBy from both get & post params as string, with default null.
     */
    public function getOrderBy(Request $request): null|string
    {
        if ($request->query->has('orderBy')) {
            return $request->query->getString('orderBy');
        }

        if ($request->request->has('orderBy')) {
            return $request->request->getString('orderBy');
        }

        return null;
    }

    /**
     * Convenience method to read orderDirection from both get & post params as string, with default null.
     */
    public function getOrderDirection(Request $request): null|string
    {
        if ($request->query->has('orderDirection')) {
            return $request->query->getString('orderDirection');
        }

        if ($request->request->has('orderDirection')) {
            return $request->request->getString('orderDirection');
        }

        return null;
    }
}
