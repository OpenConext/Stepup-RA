<?php

/**
 * Copyright 2018 SURFnet bv
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

use Surfnet\StepupBundle\Value\Provider\ViewConfigCollection;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provide translations for second factor types like yubikey, tiqr, sms, u2f, ..
 *
 * Generic tokens (gssp) are translated from the YAML configuration provided for them. Where the hard coded types (sms,
 * yubikey and u2f) are translated using the Symfony translator.
 *
 * Translations should be provided in the translations file for this project and should follow the format specified in
 * the 'translationIdFormat' field.
 */
class SecondFactorTypeTranslationService
{
    /**
     * @var ViewConfigCollection
     */
    private $gsspConfigCollection;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        ViewConfigCollection $gsspConfigCollection,
        TranslatorInterface $translator
    ) {
        $this->gsspConfigCollection = $gsspConfigCollection;
        $this->translator = $translator;
    }

    /**
     * @param string $secondFactorTypeId
     * @param string $translationIdFormat The format used to read a translation from the Symfony translator. Should be
     *                                    compatible with sprintf. Where one string parameter represents the seconf
     *                                    factor type. Example 'ra.gssp_token.%s.title'
     * @return string
     */
    public function translate($secondFactorTypeId, $translationIdFormat)
    {
        $translationId = sprintf($translationIdFormat, $secondFactorTypeId);

        if ($this->gsspConfigCollection->isGssp($secondFactorTypeId)) {
            // Attempt a gssp translation based on the gssp config
            $translation = $this->gsspConfigCollection
                ->getByIdentifier($secondFactorTypeId)
                ->getTitle();
        } else {
            // Attempt a regular symfony translation for any non gssp sf type.
            $translation = $this->translator->trans($translationId);
        }

        // If unable to translate, return the translation id, the user of this translator should decide how to handle
        // this situation.
        if ($translationId === $translation) {
            return $secondFactorTypeId;
        }

        return $translation;
    }
}
