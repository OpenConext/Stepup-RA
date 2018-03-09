<?php

/**
 * Copyright 2018 SURFnet bv
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

namespace Surfnet\StepupRa\SamlStepupProviderBundle\Provider;

use Surfnet\StepupRa\SamlStepupProviderBundle\Exception\UnknownProviderException;

/**
 * Collection of ViewConfig instances. The collection can be used to find ViewConfig objects based on their second
 * factor type id.
 */
class ViewConfigCollection
{
    /**
     * @var ViewConfig[]
     */
    private $collection = [];

    /**
     * @param ViewConfig $viewConfig
     * @param $identifier
     */
    public function addViewConfig(ViewConfig $viewConfig, $identifier)
    {
        $this->collection[$identifier] = $viewConfig;
    }

    /**
     * @param $identifier
     * @return ViewConfig
     */
    public function getByIdentifier($identifier)
    {
        if (isset($this->collection[$identifier])) {
            return $this->collection[$identifier];
        }
        throw new UnknownProviderException(
            sprintf(
                'The provider identified by "%s" can not be found in the ViewConfigCollection',
                $identifier
            )
        );
    }

    /**
     * @param $identifier
     * @return bool
     */
    public function isGssp($identifier)
    {
        return isset($this->collection[$identifier]);
    }
}
