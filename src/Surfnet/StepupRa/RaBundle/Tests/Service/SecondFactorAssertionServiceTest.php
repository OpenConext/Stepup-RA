<?php

namespace Surfnet\StepupRa\RaBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Surfnet\StepupRa\RaBundle\Service\SecondFactorAssertionService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SecondFactorAssertionServiceTest extends TestCase
{
    private $parameterBag;
    private $logger;
    private $service;

    protected function setUp(): void
    {
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new SecondFactorAssertionService($this->parameterBag, $this->logger);
    }

    /**
     * @test
     */
    public function assertSecondFactorEnabled_should_throw_exception_when_second_factor_disabled()
    {
        $type = 'sms';

        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('surfnet_stepup_ra.enabled_second_factors')
            ->willReturn(['email', 'app']);

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('A controller action was called for a disabled second factor');

        $this->expectException(NotFoundHttpException::class);

        $this->service->assertSecondFactorEnabled($type);
    }

    /**
     * @test
     */
    public function assertSecondFactorEnabled_should_not_throw_exception_when_second_factor_enabled()
    {
        $type = 'sms';

        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('surfnet_stepup_ra.enabled_second_factors')
            ->willReturn(['sms', 'app']);

        $this->logger->expects($this->never())
            ->method('warning');

        $this->service->assertSecondFactorEnabled($type);
    }
}
