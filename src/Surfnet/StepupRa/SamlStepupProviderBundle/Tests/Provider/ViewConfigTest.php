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

namespace Surfnet\StepupRa\SamlStepupProviderBundle\Tests\Provider;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Surfnet\StepupRa\SamlStepupProviderBundle\Provider\ViewConfig;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests the ViewConfig class
 * @package  Surfnet\StepupRa\SamlStepupProviderBundle\Tests\Provider
 */
final class ViewConfigTest extends TestCase
{
    /**
     * @test
     * @group di
     */
    public function view_config_translates_correctly()
    {
        $viewConfig = $this->buildViewConfig('nl_NL');

        $this->assertEquals('NL title', $viewConfig->getTitle());
        $this->assertEquals('NL pageTitle', $viewConfig->getPageTitle());
        $this->assertEquals('NL explanation', $viewConfig->getExplanation());
        $this->assertEquals('NL initiate', $viewConfig->getInitiate());
        $this->assertEquals('NL gssfIdMismatch', $viewConfig->getGssfIdMismatch());

        $viewConfig = $this->buildViewConfig('en_GB');
        $this->assertEquals('EN title', $viewConfig->getTitle());
        $this->assertEquals('EN pageTitle', $viewConfig->getPageTitle());
        $this->assertEquals('EN explanation', $viewConfig->getExplanation());
        $this->assertEquals('EN initiate', $viewConfig->getInitiate());
        $this->assertEquals('EN gssfIdMismatch', $viewConfig->getGssfIdMismatch());
    }

    /**
     * @test
     * @group di
     */
    public function translation_fails_when_no_current_language_set()
    {
        $this->expectExceptionMessage("The current language is not set");
        $this->expectException(\Surfnet\StepupRa\RaBundle\Exception\LogicException::class);

        $viewConfig = $this->buildViewConfig(null);
        $viewConfig->getExplanation();
    }

    /**
     * @test
     * @group di
     */
    public function view_config_cannot_serve_french_translations()
    {
        $this->expectExceptionMessage("The requested translation is not available in this language: fr_FR. Available languages: en_GB, nl_NL");
        $this->expectException(\Surfnet\StepupRa\RaBundle\Exception\LogicException::class);

        $viewConfig = $this->buildViewConfig('fr_FR');
        $viewConfig->getGssfIdMismatch();
    }

    /**
     * @param string $locale
     * @return ViewConfig
     */
    private function buildViewConfig($locale = '')
    {
        $request = m::mock(RequestStack::class);
        $request->shouldReceive('getCurrentRequest->getLocale')->andReturn($locale)->byDefault();
        return new ViewConfig(
            $request,
            $this->getTranslationsArray('title'),
            $this->getTranslationsArray('pageTitle'),
            $this->getTranslationsArray('explanation'),
            $this->getTranslationsArray('initiate'),
            $this->getTranslationsArray('gssfIdMismatch')
        );
    }

    /**
     * @param $string
     * @return array
     */
    private function getTranslationsArray($string)
    {
        return [
            'en_GB' => 'EN ' . $string,
            'nl_NL' => 'NL ' . $string,
        ];
    }
}
