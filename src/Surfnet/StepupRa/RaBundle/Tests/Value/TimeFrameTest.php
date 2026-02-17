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

namespace Surfnet\StepupRa\RaBundle\Tests\Value;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;
use Surfnet\StepupRa\RaBundle\Value\TimeFrame;

class TimeFrameTest extends TestCase
{
    /**
     * @param mixed $notPositiveInteger
     */
    #[Group('value')]
    #[Test]
    #[DataProvider('notPositiveIntegerProvider')]
    public function it_cannot_be_given_an_non_positive_amount_of_seconds($notPositiveInteger)
    {
        $this->expectException(InvalidArgumentException::class);

        TimeFrame::ofSeconds($notPositiveInteger);
    }

    #[Group('value')]
    #[Test]
    public function to_string_output_matches_amount_of_seconds_as_string()
    {
        $seconds = 1000;

        $timeFrame = TimeFrame::ofSeconds($seconds);

        $this->assertEquals(
            '1000',
            (string) $timeFrame,
            'The amount of seconds as string must match timeFrame::__toString',
        );
    }

    public static function notPositiveIntegerProvider(): array
    {
        return [
            'empty string' => [''],
            'string'       => ['abc'],
            'array'        => [[]],
            'float'        => [2.718],
            'zero'         => [0],
            'negative int' => [-1],
            'object'       => [new stdClass()],
        ];
    }
}
