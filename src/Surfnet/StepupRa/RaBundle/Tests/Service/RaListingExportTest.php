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

namespace Surfnet\StepupRa\RaBundle\Tests\Service;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListing;
use Surfnet\StepupRa\RaBundle\Service\RaListingExport;

class RaListingExportTest extends TestCase
{
    #[Test]
    public function it_streams_a_csv_with_a_header_row_and_a_row_per_listing()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('notice');

        $export = new RaListingExport($logger);

        $raListing = new RaListing();
        $raListing->identityId = 'identity-id';
        $raListing->commonName = 'Jane Doe';
        $raListing->email = 'jane@example.org';
        $raListing->institution = 'institution-a';
        $raListing->role = 'ra';
        $raListing->raInstitution = 'institution-a';
        $raListing->location = 'Room 101';
        $raListing->contactInformation = '+31 6 12345678';

        $response = $export->export([$raListing], 'ra_export_2026-07-24');

        $this->assertSame('application/csv', $response->headers->get('Content-Type'));
        $this->assertSame(
            'attachment; filename="ra_export_2026-07-24.csv"',
            $response->headers->get('Content-Disposition'),
        );

        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();

        $rows = array_map(
            fn(string $line) => str_getcsv($line, escape: ''),
            explode("\n", rtrim(str_replace("\r\n", "\n", $csv), "\n")),
        );

        $this->assertSame(
            ['Common Name', 'Email', 'Institution', 'Role', 'RA Institution', 'Location', 'Contact Information'],
            $rows[0],
        );
        $this->assertSame(
            ['Jane Doe', 'jane@example.org', 'institution-a', 'ra', 'institution-a', 'Room 101', '+31 6 12345678'],
            $rows[1],
        );
    }

    #[Test]
    public function it_streams_only_the_header_row_when_there_are_no_listings()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('notice');

        $export = new RaListingExport($logger);

        $response = $export->export([], 'ra-raa-export_2026-07-24');

        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();

        $rows = array_map(
            fn(string $line) => str_getcsv($line, escape: ''),
            explode("\n", rtrim(str_replace("\r\n", "\n", $csv), "\n")),
        );

        $this->assertCount(1, $rows);
        $this->assertSame(
            ['Common Name', 'Email', 'Institution', 'Role', 'RA Institution', 'Location', 'Contact Information'],
            $rows[0],
        );
    }
}
