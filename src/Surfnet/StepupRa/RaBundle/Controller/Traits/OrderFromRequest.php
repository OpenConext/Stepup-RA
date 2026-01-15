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
     * Convenience method to read get / post params as string, with default null.
     * This is a commonly used pattern in the RA controllers
     */
    private function getString(Request $request, string $paramName): null|string
    {
        if ($request->query->has($paramName)) {
            return $request->query->getString($paramName);
        }

        if ($request->request->has($paramName)) {
            return $request->request->getString($paramName);
        }

        return null;
    }
}
