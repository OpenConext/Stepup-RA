<?php

/**
 * Copyright 2016 SURFnet B.V.
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

use Surfnet\StepupRa\RaBundle\Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GlobalViewParameters
{
    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var string[]
     */
    private $supportUrl;

    /**
     * @param string[] $locales
     * @param string[] $supportUrl
     */
    public function __construct(private readonly TranslatorInterface $translator, array $locales, array $supportUrl)
    {
        Assert::keysAre($supportUrl, $locales);
        $this->locales = $locales;
        $this->supportUrl = $supportUrl;
    }

    public function getSupportUrl(): string
    {
        return $this->supportUrl[$this->translator->getLocale()];
    }

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        return $this->locales;
    }
}
