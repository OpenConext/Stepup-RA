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

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Exception for the case where a user is not granted RA privileges.
 *
 * This exception extends AuthenticationException, while technically this is
 * an authorization error. This exception is thrown from inside the SAML
 * authentication handler components, and Symfony does not allow authorization
 * there. This could be refactored but for now we accept an authentication
 * exception while it should actually be an authorization exception.
 *
 * @package Surfnet\StepupRa\RaBundle\Exception
 */
final class UserNotRaException extends AuthenticationException
{
}
