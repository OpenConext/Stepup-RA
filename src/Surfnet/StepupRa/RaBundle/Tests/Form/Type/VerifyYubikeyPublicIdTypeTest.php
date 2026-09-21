<?php

/**
 * Copyright 2015 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Tests\Form\Type;

use PHPUnit\Framework\Attributes\Test;
use Surfnet\StepupRa\RaBundle\Command\VerifyYubikeyPublicIdCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\VerifyYubikeyPublicIdType;
use Symfony\Component\Form\Test\TypeTestCase;

class VerifyYubikeyPublicIdTypeTest extends TypeTestCase
{
    #[Test]
    public function itUsesAFieldNameThatDoesNotExposeOtpToTheBrowser(): void
    {
        $form = $this->factory->create(
            VerifyYubikeyPublicIdType::class,
            new VerifyYubikeyPublicIdCommand(),
        );
        $view = $form->createView();

        self::assertArrayNotHasKey('otp', $view->children);
        self::assertArrayHasKey('yubikeyInput', $view->children);
        self::assertSame(
            'ra_verify_yubikey_public_id[yubikeyInput]',
            $view->children['yubikeyInput']->vars['full_name'],
        );
        self::assertSame(
            'ra_verify_yubikey_public_id_yubikeyInput',
            $view->children['yubikeyInput']->vars['id'],
        );
        self::assertStringNotContainsString(
            'otp',
            strtolower($view->children['yubikeyInput']->vars['id']),
        );
    }

    #[Test]
    public function itMapsTheNeutralFieldNameToTheCommand(): void
    {
        $command = new VerifyYubikeyPublicIdCommand();
        $form = $this->factory->create(VerifyYubikeyPublicIdType::class, $command);

        $form->submit(['yubikeyInput' => 'ccccccddeeff001122334455']);

        self::assertTrue($form->isSynchronized());
        self::assertSame('ccccccddeeff001122334455', $command->yubikeyInput);
    }
}
