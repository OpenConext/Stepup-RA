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

namespace Surfnet\StepupRa\RaBundle\Service;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListing;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RaListingExport
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * @param iterable<RaListing> $raListings
     */
    public function export(iterable $raListings, string $fileName): StreamedResponse
    {
        $this->logger->notice(sprintf('Starting RA(A) listing export to "%s"', $fileName));

        $columnNames = $this->getColumnNames();

        return new StreamedResponse(
            function () use ($raListings, $columnNames, $fileName) {
                $handle = fopen('php://output', 'r+');
                if ($handle === false) {
                    throw new RuntimeException('Unable to open php://output for writing the RA(A) listing export');
                }
                fputcsv($handle, $columnNames, escape: '');

                $rowCount = 0;
                foreach ($raListings as $raListing) {
                    fputcsv($handle, [
                        $this->sanitizeCsvCell($raListing->commonName),
                        $this->sanitizeCsvCell($raListing->email),
                        $this->sanitizeCsvCell($raListing->institution),
                        $this->sanitizeCsvCell($raListing->role),
                        $this->sanitizeCsvCell($raListing->raInstitution),
                        $this->sanitizeCsvCell($raListing->location),
                        $this->sanitizeCsvCell($raListing->contactInformation),
                    ], escape: '');
                    $rowCount++;
                }

                $this->logger->notice(sprintf('Exported %d rows to "%s"', $rowCount, $fileName));

                fflush($handle);
                fclose($handle);
            },
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/csv',
                'Content-Disposition' => sprintf('attachment; filename="%s.csv"', $fileName),
            ],
        );
    }

    private function sanitizeCsvCell(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (in_array($value[0], ['=', '-', '+', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * @return string[]
     */
    private function getColumnNames(): array
    {
        return [
            'Common Name',
            'Email',
            'Institution',
            'Role',
            'RA Institution',
            'Location',
            'Contact Information',
        ];
    }
}
