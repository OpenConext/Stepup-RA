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

namespace Surfnet\StepupRa\SamlStepupProviderBundle\Exception;

use InvalidArgumentException as CoreInvalidArgumentException;

class InvalidArgumentException extends CoreInvalidArgumentException
{
    /**
     * @param string $expectedType
     * @param string $parameter
     * @param mixed $value
     * @return self
     */
    public static function invalidType(string $expectedType, string $parameter, $value): self
    {
        return new self(
            sprintf(
                'Invalid Argument, parameter "%s" should be of type "%s", "%s" given',
                $parameter,
                $expectedType,
                is_object($value) ? get_class($value) : gettype($value)
            )
        );
    }
}
