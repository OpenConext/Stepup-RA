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

namespace Surfnet\StepupRa\RaBundle\Command;

use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Surfnet\StepupRa\RaBundle\Form\Type\VettingTypeHintType;
use function property_exists;

/**
 * This command is coupled to the VettingTypeHintType
 *
 * The vetting hint type creates a set of hint textareas that can be filled by
 * a RA, filled with an institution specific hint on what type of vetting is
 * advised.
 *
 * The form type will render a number of textareas, based on the locales configured
 * on the application.
 *
 * See '%locales%' parameter in parameters.yaml
 */
class VettingTypeHintCommand
{

    /**
     * @var string
     */
    public $identityId;

    /**
     * @var string
     */
    public $institution;

    /**
     * @var string[]
     */
    public $locales;

    /**
     * @var string[]
     */
    public $hints = [];

    /**
     * The translatable hints are set, using the PHP magic setter
     */
    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
            return;
        }
        $this->assertValidLanguageInName($name);
        $locale = $this->extractLocaleFromFormFieldName($name);
        $this->hints[$locale] = $value;
    }

    public function __get($name): string
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        $this->assertValidLanguageInName($name);
        $locale = $this->extractLocaleFromFormFieldName($name);
        if ($this->__isset($name)) {
            return $this->hints[$locale] ?: '';
        }
        return '';
    }

    public function __isset($name): bool
    {
        try {
            $this->assertValidLanguageInName($name);
            $locale = $this->extractLocaleFromFormFieldName($name);
        } catch (RuntimeException) {
            return false;
        }
        return array_key_exists($locale, $this->hints);
    }

    /**
     * Based on the languages that are configured on the application eg: nl_NL or en_EN,..
     * test if the passed parameter contains a valid language identifier.
     */
    private function assertValidLanguageInName($name)
    {
        $locale = $this->extractLocaleFromFormFieldName($name);
        if (!in_array($locale, $this->locales, true)) {
            throw new RuntimeException(
                sprintf(
                    'An invalid language ("%s") was rendered on the VettingTypeHintType form. ' .
                    'Unable to process it in VettingTypeHintCommand. Configure it in the ' .
                    'parameters.yaml or investigate why this rogue language ended up on the form.',
                    $locale,
                ),
            );
        }
    }

    private function extractLocaleFromFormFieldName(string $name): string
    {
        if (empty($this->locales)) {
            throw new RuntimeException(
                'No locales have been configured on the command yet, unable to process vetting type hints',
            );
        }

        $prefix = VettingTypeHintType::HINT_TEXTAREA_NAME_PREFIX;
        $matches = [];
        preg_match('/'.$prefix.'(.*)/', $name, $matches);
        if (!array_key_exists(1, $matches)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to extract a locale from the form field name "%s". The field name prefix ' .
                    'did not match the configured value "%s"',
                    $name,
                    $prefix,
                ),
            );
        }
        return $matches[1];
    }

    public function setHints(array $hints)
    {
        foreach ($hints as $hint) {
            $this->hints[$hint['locale']] = $hint['hint'];
        }
    }
}
