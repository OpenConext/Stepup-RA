<?php

/**
 * Copyright 2017 SURFnet B.V.
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

use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\VerifiedSecondFactor;
use Surfnet\StepupRa\RaBundle\Command\StartVettingProcedureCommand;
use Surfnet\StepupRa\RaBundle\Service\VettingService;

final class VettingServiceTest extends TestCase
{
    #[Group('vetting')]
    #[Test]
    #[DataProvider('validRegistrationDatesProvider')]
    public function registration_code_is_valid_within_two_weeks_after_verification($registrationRequestedAt)
    {
        $command = new StartVettingProcedureCommand();
        $command->secondFactor = new VerifiedSecondFactor();
        $command->secondFactor->registrationRequestedAt = $registrationRequestedAt;

        $service = Mockery::mock(VettingService::class)->makePartial();

        $this->assertFalse(
            $service->isExpiredRegistrationCode($command),
        );
    }

    public static function validRegistrationDatesProvider(): array
    {
        return [
            [date_create('- 1 week')],
            [date_create('- 2 weeks')],
            [date_create('- 2 weeks')->setTime(0, 0, 0)],
            [date_create('- 2 weeks')->setTime(23, 59, 59)],
        ];
    }

    #[Group('vetting')]
    #[Test]
    #[DataProvider('expiredRegistrationDatesProvider')]
    public function registration_code_is_invalid_two_weeks_after_verification($registrationRequestedAt)
    {
        $command = new StartVettingProcedureCommand();
        $command->secondFactor = new VerifiedSecondFactor();
        $command->secondFactor->registrationRequestedAt = $registrationRequestedAt;

        $service = Mockery::mock(VettingService::class)->makePartial();

        $this->assertTrue(
            $service->isExpiredRegistrationCode($command),
        );
    }

    public static function expiredRegistrationDatesProvider(): array
    {
        return [
            [date_create('- 3 weeks')],
            [date_create('- 15 days')->setTime(0, 0, 0)],
            [date_create('- 15 days')->setTime(23, 59, 59)],
        ];
    }
}
