<?php

/**
 * Copyright 2026 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Tests\Controller\Traits;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Surfnet\StepupRa\RaBundle\Controller\Traits\OrderFromRequest;
use Symfony\Component\HttpFoundation\Request;

final class OrderFromRequestTest extends TestCase
{
    private $traitObject;

    protected function setUp(): void
    {
        $this->traitObject = new class {
            use OrderFromRequest;
        };
    }

    #[Test]
    public function it_returns_value_from_query_parameter(): void
    {
        $request = new Request(['orderBy' => 'name']);
        $result = $this->traitObject->getOrderBy($request);

        $this->assertSame('name', $result);
    }

    #[Test]
    public function it_returns_value_from_request_parameter(): void
    {
        $request = new Request([], ['orderBy' => 'date']);
        $result = $this->traitObject->getOrderBy($request);

        $this->assertSame('date', $result);
    }

    #[Test]
    public function it_returns_null_when_parameter_not_found(): void
    {
        $request = new Request();
        $result = $this->traitObject->getOrderBy($request);

        $this->assertNull($result);
    }

    #[Test]
    public function it_prioritizes_query_parameter_over_request_parameter(): void
    {
        $request = new Request(['orderBy' => 'query_value'], ['orderBy' => 'request_value']);

        $result = $this->traitObject->getOrderBy($request);

        $this->assertSame('query_value', $result);
    }

    #[Test]
    public function it_handles_empty_string_values(): void
    {
        $request = new Request(['orderBy' => '']);
        $result = $this->traitObject->getOrderBy($request);

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_handles_numeric_string_values(): void
    {
        $request = new Request(['orderBy' => '42']);
        $result = $this->traitObject->getOrderBy($request);

        $this->assertSame('42', $result);
    }

    #[Test]
    public function it_returns_order_direction_from_query_parameter(): void
    {
        $request = new Request(['orderDirection' => 'asc']);
        $result = $this->traitObject->getOrderDirection($request);

        $this->assertSame('asc', $result);
    }
}
