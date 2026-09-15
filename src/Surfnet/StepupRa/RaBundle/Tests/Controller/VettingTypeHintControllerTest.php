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

namespace Surfnet\StepupRa\RaBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Surfnet\StepupBundle\Value\Loa;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Profile;
use Surfnet\StepupRa\RaBundle\Controller\VettingTypeHintController;
use Surfnet\StepupRa\RaBundle\Security\AuthenticatedIdentity;
use Surfnet\StepupRa\RaBundle\Service\InstitutionListingService;
use Surfnet\StepupRa\RaBundle\Service\ProfileService;
use Surfnet\StepupRa\RaBundle\Service\VettingTypeHintService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * Regression tests for the vetting-type-hint save flow: a successful save
 * must redirect using the institution the server already resolved for the
 * RAA, never the client-submitted hidden form field, and a failed save must
 * re-render the form without losing the submitted hints.
 */
class VettingTypeHintControllerTest extends TestCase
{
    private const HOME_INSTITUTION = 'example.org';

    public function test_successful_save_redirects_using_the_trusted_institution_not_the_submitted_hidden_field()
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->with('vetting_type_hint', ['institution' => self::HOME_INSTITUTION])
            ->willReturn('/vetting-type-hint?institution=' . self::HOME_INSTITUTION);

        $vettingTypeHintService = $this->createMock(VettingTypeHintService::class);
        $vettingTypeHintService->method('findBy')->willReturn(null);
        $vettingTypeHintService->expects($this->once())->method('save')->willReturn(true);

        $twig = $this->createMock(Environment::class);
        $twig->expects($this->never())->method('render');

        [$controller, $request] = $this->createControllerAndRequest(
            $router,
            $vettingTypeHintService,
            $twig,
            // A tampered hidden field: the RAA is only authorized for HOME_INSTITUTION.
            'attacker.example',
        );

        $response = $controller->vettingTypeHint($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            '/vetting-type-hint?institution=' . self::HOME_INSTITUTION,
            $response->getTargetUrl(),
        );
        $this->assertTrue($request->getSession()->getFlashBag()->has('success'));
    }

    public function test_failed_save_rerenders_the_form_keeping_the_submitted_hints()
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects($this->never())->method('generate');

        $vettingTypeHintService = $this->createMock(VettingTypeHintService::class);
        $vettingTypeHintService->method('findBy')->willReturn(null);
        $vettingTypeHintService->expects($this->once())->method('save')->willReturn(false);

        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturnCallback(
            static function (string $view, array $parameters = []): string {
                $hintFormView = $parameters['hintForm'];

                return (string) $hintFormView['vetting_type_hint_en_GB']->vars['value'];
            },
        );

        [$controller, $request] = $this->createControllerAndRequest(
            $router,
            $vettingTypeHintService,
            $twig,
            self::HOME_INSTITUTION,
            'Please bring a valid passport',
        );

        $response = $controller->vettingTypeHint($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Please bring a valid passport', $response->getContent());
        $this->assertTrue($request->getSession()->getFlashBag()->has('error'));
    }

    /**
     * @return array{0: VettingTypeHintController, 1: Request}
     */
    private function createControllerAndRequest(
        UrlGeneratorInterface $router,
        VettingTypeHintService $vettingTypeHintService,
        Environment $twig,
        string $submittedInstitution,
        string $submittedHint = 'A hint',
    ): array {
        $identity = Identity::fromData([
            'id' => 'identity-1',
            'name_id' => 'urn:collab:person:example.org:raa',
            'institution' => self::HOME_INSTITUTION,
            'email' => 'raa@example.org',
            'common_name' => 'Test RAA',
            'preferred_locale' => 'en_GB',
        ]);
        $authenticatedIdentity = new AuthenticatedIdentity($identity, new Loa(Loa::LOA_2, 'loa2'), ['ROLE_RAA']);

        $profile = Profile::fromData([
            'id' => $identity->id,
            'name_id' => $identity->nameId,
            'institution' => self::HOME_INSTITUTION,
            'email' => $identity->email,
            'common_name' => $identity->commonName,
            'preferred_locale' => $identity->preferredLocale,
            // Single authorized institution, so no institution-select form is rendered.
            'authorizations' => [self::HOME_INSTITUTION => ['raa']],
            'is_sraa' => false,
        ]);

        $profileService = $this->createMock(ProfileService::class);
        $profileService->method('findByIdentityId')->willReturn($profile);

        $controller = new VettingTypeHintController(
            $this->createMock(LoggerInterface::class),
            $this->createMock(InstitutionListingService::class),
            $profileService,
            $vettingTypeHintService,
            ['en_GB'],
        );

        $request = Request::create('/vetting-type-hint', 'POST', [
            'vetting_type_hint' => [
                'institution' => $submittedInstitution,
                'vetting_type_hint_en_GB' => $submittedHint,
                'continue' => '',
            ],
        ]);
        $request->setSession(new Session(new MockArraySessionStorage()));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->willReturn(false);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($authenticatedIdentity);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $container = new Container();
        $container->set('router', $router);
        $container->set('twig', $twig);
        $container->set(
            'form.factory',
            Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory(),
        );
        $container->set('request_stack', $requestStack);
        $container->set('security.authorization_checker', $authorizationChecker);
        $container->set('security.token_storage', $tokenStorage);
        $controller->setContainer($container);

        return [$controller, $request];
    }
}
