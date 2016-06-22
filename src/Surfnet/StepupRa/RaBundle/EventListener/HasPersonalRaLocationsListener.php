<?php

namespace Surfnet\StepupRa\RaBundle\EventListener;

use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupRa\RaBundle\Security\Authentication\Token\SamlToken;
use Surfnet\StepupRa\RaBundle\Service\InstitutionWithPersonalRaLocationsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\SecurityContext;
use Twig_Environment;

final class HasPersonalRaLocationsListener implements EventSubscriberInterface
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var InstitutionWithPersonalRaLocationsService
     */
    private $service;

    /**
     * @var SecurityContext
     */
    private $securityContext;

    public function __construct(
        Twig_Environment $twig,
        InstitutionWithPersonalRaLocationsService $institutionWithPersonalRaLocationsService,
        SecurityContext $securityContext
    ) {
        $this->twig = $twig;
        $this->service = $institutionWithPersonalRaLocationsService;
        $this->securityContext = $securityContext;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $token = $this->securityContext->getToken();

        if (!$token instanceof SamlToken) {
            return;
        }

        $user = $token->getUser();

        if (!$user instanceof Identity) {
            return;
        }

        $hasPersonalRaLocations = $this->service->institutionHasPersonalRaLocations($user->institution);
        $this->twig->addGlobal('hasPersonalRaLocations', $hasPersonalRaLocations);
    }

    public static function getSubscribedEvents()
    {
        return [
            // The firewall, which makes the token available, listens at P8
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }
}
