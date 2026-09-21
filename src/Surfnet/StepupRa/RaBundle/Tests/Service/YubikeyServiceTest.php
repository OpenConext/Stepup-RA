<?php

/**
 * Copyright 2024 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Tests\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyOtpCommand;
use Surfnet\StepupRa\RaBundle\Service\YubikeyService;

class YubikeyServiceTest extends TestCase
{
    #[Test]
    public function itSendsTheNeutralInputUnderTheExternalOtpKey(): void
    {
        /** @var list<array{request: \Psr\Http\Message\RequestInterface}> $history */
        $history = [];
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], '{"status":"OK"}'),
            ]),
        );
        $handlerStack->push(Middleware::history($history));

        $service = new YubikeyService(
            new Client([
                'base_uri' => 'https://gateway.example.test/',
                'handler' => $handlerStack,
            ]),
            new NullLogger(),
        );
        $command = new VerifyYubikeyOtpCommand();
        $command->yubikeyInput = 'ccccccddeeff001122334455';
        $command->identityId = 'identity-id';
        $command->institution = 'institution.example';

        $service->verify($command);

        self::assertCount(1, $history);
        /** @var array{otp: array{value: string}} $payload */
        $payload = json_decode(
            (string) $history[0]['request']->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        self::assertSame('ccccccddeeff001122334455', $payload['otp']['value']);
        self::assertArrayNotHasKey('yubikeyInput', $payload);
    }
}
