<?php

/**
 * Copyright 2017 SURFnet bv
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
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaSecondFactorExportCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RaSecondFactorExport
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function export(RaSecondFactorExportCollection $collection, $fileName): StreamedResponse
    {
        $this->logger->notice(sprintf('Exporting %d rows to "%s"', $collection->count(), $fileName));

        $keys = array_keys($collection->getColumnNames());

        return new StreamedResponse(
            function () use ($collection, $keys) {
                $handle = fopen('php://output', 'r+');
                fputcsv($handle, $collection->getColumnNames());
                foreach ($collection->getElements() as $row) {
                    $cells = [];
                    $array = (array)$row;
                    foreach ($keys as $key) {
                        $cells[$key] = $array[$key];
                    }
                    fputcsv($handle, $cells);
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
}
