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

namespace Surfnet\StepupRa\RaBundle\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    public static function invalidType(string $expected, string $parameterName, mixed $parameter): InvalidArgumentException
    {
        $message = sprintf(
            'Invalid argument type: "%s" expected, "%s" given for "%s"',
            $expected,
            get_debug_type($parameter),
            $parameterName,
        );

        return new self($message);
    }
}
