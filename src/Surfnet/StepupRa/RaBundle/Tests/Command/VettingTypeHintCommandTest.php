<?php

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Surfnet\StepupRa\RaBundle\Command\VettingTypeHintCommand;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Surfnet\StepupRa\RaBundle\Form\Type\VettingTypeHintType;

/**
 * Given the use of the magic getters and setters this command is
 * covered in extra tests. Hopefully demystifying the magic factor.
 */
class VettingTypeHintCommandTest extends TestCase
{
    public function test_setting_hints_is_allowed()
    {
        $command = new VettingTypeHintCommand();
        $command->locales = [0 => "nl_NL", 1 => "en_GB"];
        $command->__set(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'nl_NL', 'foobar');
        $command->__set(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'en_GB', 'foobar');
        $expectedData = [
            'nl_NL' => 'foobar',
            'en_GB' => 'foobar',
        ];

        $this->assertEquals($expectedData, $command->hints);
    }

    public function test_empty_hints_are_allowed()
    {
        $command = new VettingTypeHintCommand();
        $command->locales = [0 => "nl_NL", 1 => "en_GB"];
        $command->__set(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'nl_NL', '');
        $command->__set(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'en_GB', '');
        $expectedData = [
            'nl_NL' => '',
            'en_GB' => '',
        ];

        $this->assertEquals($expectedData, $command->hints);
    }

    public function test_invalid_locales_are_not_allowed()
    {
        $command = new VettingTypeHintCommand();
        $command->locales = [0 => "nl_NL", 1 => "en_GB"];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('An invalid language ("en_US") was rendered on the VettingTypeHintType form. Unable to process it in VettingTypeHintCommand. Configure it in the parameters.yaml or investigate why this rogue language ended up on the form.');
        $command->__set(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'en_US', 'foobar');
    }

    public function test_invalid_form_field_name_prefix_is_not_allowed()
    {
        $command = new VettingTypeHintCommand();
        $command->locales = [0 => "nl_NL", 1 => "en_GB"];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to extract a locale from the form field name "sad_prefix_en_GB". The field name prefix did not match the configured value "vetting_type_hint_');
        $command->__set('sad_prefix_en_GB', 'foobar');
    }

    public function test_invalid_when_no_locales_configured()
    {
        $command = new VettingTypeHintCommand();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No locales have been configured on the command yet, unable to process vetting type hints');
        $command->__set(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'en_GB', '');
    }

    public function test_get_hint()
    {
        $command = new VettingTypeHintCommand();
        $command->locales = [0 => "nl_NL", 1 => "en_GB"];
        $command->hints = [
            'nl_NL' => 'foobar',
            'en_GB' => 'foobar',
        ];

        $this->assertEquals('foobar', $command->__get(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'nl_NL'));
        $this->assertEquals('foobar', $command->__get(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'en_GB'));
    }

    public function test_get_hint_not_set_yields_empty_string()
    {
        $command = new VettingTypeHintCommand();
        $command->locales = [0 => "nl_NL", 1 => "en_GB"];
        $command->hints = [
            'en_GB' => 'foobar',
        ];

        $this->assertEquals('', $command->__get(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'nl_NL'));
        $this->assertEquals('foobar', $command->__get(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'en_GB'));
    }

    public function test_get_must_use_existing_prefix()
    {
        $command = new VettingTypeHintCommand();
        $command->locales = [0 => "nl_NL", 1 => "en_GB"];
        $command->hints = [
            'en_GB' => 'foobar',
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to extract a locale from the form field name "sad_prefix_nl_NL". The field name prefix did not match the configured value "vetting_type_hint_"');
        $command->__get('sad_prefix_nl_NL');
    }

    public function test_isset_hint()
    {
        $command = new VettingTypeHintCommand();
        $command->locales = [0 => "nl_NL", 1 => "en_GB"];
        $command->hints = [
            'nl_NL' => 'foobar',
            'en_GB' => 'foobar',
        ];

        $this->assertTrue($command->__isset(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'nl_NL'));
        $this->assertTrue($command->__isset(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'en_GB'));
        $this->assertFalse($command->__isset(VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX . 'en_US'));
        $this->assertFalse($command->__isset('sad_prefix_en_US'));
        $this->assertFalse($command->__isset(''));
        $this->assertFalse($command->__isset(false));
        $this->assertFalse($command->__isset('nl_NL'));
    }
}
