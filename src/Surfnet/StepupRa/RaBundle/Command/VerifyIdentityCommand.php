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

namespace Surfnet\StepupRa\RaBundle\Command;

use Symfony\Component\Validator\Constraints as Assert;

class VerifyIdentityCommand
{
    #[Assert\NotBlank(
        message: 'ra.verify_identity_command.document_number.may_not_be_empty',
    )]
    #[Assert\Type(
        type: 'string',
        message: 'ra.verify_identity_command.document_number.must_be_string',
    )]
    #[Assert\Length(
        min: 1,
        max: 6,
        minMessage: 'ra.verify_identity_command.document_number.must_be_higher_than_minimum',
        maxMessage: 'ra.verify_identity_command.document_number.must_be_lower_than_maximum',
    )]
    public string $documentNumber;

    #[Assert\EqualTo(
        value: true,
        message: 'ra.verify_identity_command.identity_verified.must_be_checked',
    )]
    public bool $identityVerified;
}
