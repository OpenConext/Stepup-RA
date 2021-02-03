<?php

/**
 * Copyright 2016 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Value;

use DateInterval;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;

final class TimeFrame
{
    /**
     * @var DateInterval
     */
    private $timeFrame;

    /**
     * @param DateInterval $timeFrame
     */
    final private function __construct(DateInterval $timeFrame)
    {
        $this->timeFrame = $timeFrame;
    }

    /**
     * @param int $seconds
     * @return TimeFrame
     */
    public static function ofSeconds(int $seconds): TimeFrame
    {
        return new TimeFrame(new DateInterval('PT' . $seconds . 'S'));
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     */
    public function getEndWhenStartingAt(DateTime $dateTime): DateTime
    {
        return $dateTime->add($this->timeFrame);
    }

    /**
     * @param TimeFrame $other
     * @return bool
     */
    public function equals(TimeFrame $other): bool
    {
        return $this->timeFrame->s === $other->timeFrame->s;
    }

    public function __toString(): string
    {
        return $this->timeFrame->format('%S');
    }
}
