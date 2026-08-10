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

use DateTime;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Surfnet\StepupMiddlewareClient\Identity\Dto\RaListingSearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListing;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RaListingCollection;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\RaListingService as ApiRaListingService;
use Surfnet\StepupRa\RaBundle\Command\ExportRaListingCommand;
use Surfnet\StepupRa\RaBundle\Service\RaListingExport;
use Surfnet\StepupRa\RaBundle\Service\RaListingService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RaListingServiceTest extends TestCase
{
    #[Test]
    public function export_pages_through_all_results_and_hands_the_full_set_to_the_exporter()
    {
        $firstPage = $this->collectionOf(['identity-1', 'identity-2'], totalItems: 3, page: 1, itemsPerPage: 2);
        $secondPage = $this->collectionOf(['identity-3'], totalItems: 3, page: 2, itemsPerPage: 2);

        $apiService = Mockery::mock(ApiRaListingService::class);
        $apiService
            ->shouldReceive('search')
            ->once()
            ->with(Mockery::on(fn (RaListingSearchQuery $query) => str_contains($query->toHttpQuery(), 'p=1')))
            ->andReturn($firstPage);
        $apiService
            ->shouldReceive('search')
            ->once()
            ->with(Mockery::on(fn (RaListingSearchQuery $query) => str_contains($query->toHttpQuery(), 'p=2')))
            ->andReturn($secondPage);

        $expectedResponse = Mockery::mock(StreamedResponse::class);
        $export = Mockery::mock(RaListingExport::class);
        $export
            ->shouldReceive('export')
            ->once()
            ->with(
                Mockery::on(function (array $listings) {
                    return array_map(fn (RaListing $listing) => $listing->identityId, $listings)
                        === ['identity-1', 'identity-2', 'identity-3'];
                }),
                'ra-raa-export_' . (new DateTime())->format('Y-m-d'),
            )
            ->andReturn($expectedResponse);

        $service = new RaListingService($apiService, $export);

        $command = new ExportRaListingCommand();
        $command->actorId = 'actor-id';

        $this->assertSame($expectedResponse, $service->export($command));
    }

    #[Test]
    public function export_only_queries_a_single_page_when_there_are_no_results()
    {
        $emptyPage = RaListingCollection::empty();

        $apiService = Mockery::mock(ApiRaListingService::class);
        $apiService
            ->shouldReceive('search')
            ->once()
            ->andReturn($emptyPage);

        $expectedResponse = Mockery::mock(StreamedResponse::class);
        $export = Mockery::mock(RaListingExport::class);
        $export
            ->shouldReceive('export')
            ->once()
            ->with([], Mockery::type('string'))
            ->andReturn($expectedResponse);

        $service = new RaListingService($apiService, $export);

        $command = new ExportRaListingCommand();
        $command->actorId = 'actor-id';

        $this->assertSame($expectedResponse, $service->export($command));
    }

    /**
     * @param string[] $identityIds
     */
    private function collectionOf(
        array $identityIds,
        int $totalItems,
        int $page,
        int $itemsPerPage,
    ): RaListingCollection {
        $elements = array_map(function (string $identityId) {
            $listing = new RaListing();
            $listing->identityId = $identityId;
            $listing->commonName = $identityId;
            $listing->email = $identityId . '@example.org';
            $listing->institution = 'institution-a';
            $listing->role = 'ra';
            $listing->raInstitution = 'institution-a';
            $listing->location = '';
            $listing->contactInformation = '';

            return $listing;
        }, $identityIds);

        return new RaListingCollection($elements, $totalItems, $page, $itemsPerPage);
    }
}
