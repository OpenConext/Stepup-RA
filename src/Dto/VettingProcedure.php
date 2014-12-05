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

namespace Surfnet\StepupRa\RaBundle\Dto;

use Surfnet\StepupMiddlewareClientBundle\Uuid\Uuid;

class VettingProcedure
{
    /**
     * @var string
     */
    public $uuid;

    /**
     * @var string|null
     */
    public $identityId;

    /**
     * @var string|null
     */
    public $institution;

    /**
     * @var string|null
     */
    public $commonName;

    /**
     * @var string|null
     */
    public $registrationCode;

    /**
     * @var string|null
     */
    public $secondFactorType;

    /**
     * @var string|null
     */
    public $expectedSecondFactorIdentifier;

    /**
     * @var string|null
     */
    public $inputSecondFactorIdentifier;
    /**
     * @var boolean|null
     */
    public $identityVerified;

    /**
     * @return self
     */
    public static function start()
    {
        $procedure = new self();
        $procedure->uuid = Uuid::generate();

        return $procedure;
    }

    final private function __construct()
    {
    }
}
