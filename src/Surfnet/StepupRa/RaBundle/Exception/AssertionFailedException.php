<?php

/**
 * Copyright 2016 SURFnet B.V.
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

use Assert\AssertionFailedException as AssertAssertionFailedException;

class AssertionFailedException extends InvalidArgumentException implements AssertAssertionFailedException
{
    // @codingStandardsIgnoreStart Compliance with beberlei/assert's invalid argument exception
    public function __construct($message, $code, private $value, private $propertyPath = null, private readonly array $constraints = [])
    {
        parent::__construct($message, $code);
    }
    // @codingStandardsIgnoreEnd

    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
