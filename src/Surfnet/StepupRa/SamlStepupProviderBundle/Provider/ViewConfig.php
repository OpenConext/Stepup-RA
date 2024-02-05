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

namespace Surfnet\StepupRa\SamlStepupProviderBundle\Provider;

use Surfnet\StepupBundle\Value\Provider\ViewConfigInterface;
use Surfnet\StepupRa\RaBundle\Exception\LogicException;
use Symfony\Component\HttpFoundation\RequestStack;

class ViewConfig implements ViewConfigInterface
{
    /**
     * The arrays are arrays of translated text, indexed on locale.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        public array $title,
        public array $pageTitle,
        public array $explanation,
        public array $initiate,
        public array $gssfIdMismatch,
    ) {
    }

    public function getTitle(): string
    {
        return $this->getTranslation($this->title);
    }

    public function getExplanation(): string
    {
        return $this->getTranslation($this->explanation);
    }

    public function getGssfIdMismatch(): string
    {
        return $this->getTranslation($this->gssfIdMismatch);
    }

    public function getInitiate(): string
    {
        return $this->getTranslation($this->initiate);
    }

    public function getPageTitle(): string
    {
        return $this->getTranslation($this->pageTitle);
    }

    /**
     * @throws LogicException
     */
    private function getTranslation(array $translations): mixed
    {
        $currentLocale = $this->requestStack->getCurrentRequest()->getLocale();
        if (is_null($currentLocale)) {
            throw new LogicException('The current language is not set');
        }
        if (isset($translations[$currentLocale])) {
            return $translations[$currentLocale];
        }
        throw new LogicException(
            sprintf(
                'The requested translation is not available in this language: %s. Available languages: %s',
                $currentLocale,
                implode(', ', array_keys($translations)),
            ),
        );
    }
}
