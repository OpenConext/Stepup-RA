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
     * @var array
     */
    public $title;

    /**
     * @var array
     */
    public $pageTitle;

    /**
     * @var array
     */
    public $explanation;

    /**
     * @var array
     */
    public $initiate;

    /**
     * @var array
     */
    public $gssfIdMismatch;

    /**
     * The arrays are arrays of translated text, indexed on locale.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        array $title,
        array $pageTitle,
        array $explanation,
        array $initiate,
        array $gssfIdMismatch,
    ) {
        $this->title = $title;
        $this->pageTitle = $pageTitle;
        $this->explanation = $explanation;
        $this->initiate = $initiate;
        $this->gssfIdMismatch = $gssfIdMismatch;
    }

    /**
     * @return array
     */
    public function getTitle()
    {
        return $this->getTranslation($this->title);
    }

    /**
     * @return array
     */
    public function getExplanation()
    {
        return $this->getTranslation($this->explanation);
    }

    /**
     * @return array
     */
    public function getGssfIdMismatch()
    {
        return $this->getTranslation($this->gssfIdMismatch);
    }

    /**
     * @return array
     */
    public function getInitiate()
    {
        return $this->getTranslation($this->initiate);
    }

    /**
     * @return array
     */
    public function getPageTitle()
    {
        return $this->getTranslation($this->pageTitle);
    }

    /**
     * @return mixed
     * @throws LogicException
     */
    private function getTranslation(array $translations)
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
