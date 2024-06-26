<?php

/**
 * Copyright 2015 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Command;

use Symfony\Component\Validator\Constraints as Assert;

class VerifyYubikeyPublicIdCommand
{
    /**
     *
     * @var string
     */
    #[Assert\NotBlank(message: 'ra.verify_yubikey_command.otp.may_not_be_empty')]
    #[Assert\Type(type: 'string', message: 'ra.verify_yubikey_command.otp.must_be_string')]
    public $otp;

    /**
     * @var string Filled in by the VettingService.
     */
    public $expectedPublicId;

    /**
     * The requesting identity's ID (not name ID).
     *
     * @var string Filled in by the VettingService.
     */
    public $identityId;

    /**
     * The requesting identity's institution.
     *
     * @var string Filled in by the VettingService.
     */
    public $institution;
}
