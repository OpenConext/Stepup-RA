<?php

namespace Surfnet\StepupRa\RaBundle\Service;

use Surfnet\StepupMiddlewareClientBundle\Configuration\Service\InstitutionWithPersonalRaLocationsService
    as ApiInstitutionWithPersonalRaLocationsService;

class InstitutionWithPersonalRaLocationsService
{
    /**
     * @var ApiInstitutionWithPersonalRaLocationsService
     */
    private $service;

    public function __construct(ApiInstitutionWithPersonalRaLocationsService $service)
    {
        $this->service = $service;
    }

    /**
     * @param string $institution
     * @return bool
     */
    public function institutionShowsRaLocations($institution)
    {
        return $this->service->institutionShowsRaLocations($institution);
    }
}
