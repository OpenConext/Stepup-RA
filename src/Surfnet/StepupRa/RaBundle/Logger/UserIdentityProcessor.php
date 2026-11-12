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

namespace Surfnet\StepupRa\RaBundle\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Surfnet\StepupRa\RaBundle\Security\AuthenticatedIdentity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Adds the id of the authenticated identity to every log record, so log lines can be attributed to a user.
 *
 * Implementing ProcessorInterface makes the security bundle inject the untracked token storage, so reading the token
 * while logging does not mark the session as used.
 */
final readonly class UserIdentityProcessor implements ProcessorInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof AuthenticatedIdentity) {
            $record->extra['user_id'] = $user->getId();
        }

        return $record;
    }
}
