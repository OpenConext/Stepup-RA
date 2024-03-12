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

namespace Surfnet\StepupRa\RaBundle\Service;

use Surfnet\StepupRa\RaBundle\Service\Gssf\VerificationResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

final readonly class GssfService
{
    public function __construct(
//        private AttributeBagInterface $state
    private RequestStack $requestStack,
    )
    {
    }

    public function startVerification(string $gssfId, string $procedureId): void
    {
        $this->requestStack->getSession()->set('current_verification', ['procedureId' => $procedureId, 'gssfId' => $gssfId]);
    }

    public function verify(string $gssfId): VerificationResult
    {
        $verification = $this->requestStack->getSession()->remove('current_verification');

        if (!$verification) {
            return VerificationResult::noSuchProcedure();
        }

        if ($gssfId === $verification['gssfId']) {
            return VerificationResult::verificationSucceeded($verification['procedureId']);
        }

        return VerificationResult::verificationFailed($verification['procedureId']);
    }
}
