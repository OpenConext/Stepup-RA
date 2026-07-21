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
     * @param RaListing[] $raListings
     */
    public function export(array $raListings, string $fileName): StreamedResponse
    {
        $this->logger->notice(sprintf('Exporting %d rows to "%s"', count($raListings), $fileName));

        $columnNames = $this->getColumnNames();

        return new StreamedResponse(
            function () use ($raListings, $columnNames) {
                $handle = fopen('php://output', 'r+');
                if ($handle === false) {
                    throw new RuntimeException('Unable to open php://output for writing the RA(A) listing export');
                }
                fputcsv($handle, $columnNames);
                foreach ($raListings as $raListing) {
                    fputcsv($handle, [
                        $raListing->commonName,
                        $raListing->email,
                        $raListing->institution,
                        $raListing->role,
                        $raListing->raInstitution,
                        $raListing->location,
                        $raListing->contactInformation,
                    ]);
                }
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
