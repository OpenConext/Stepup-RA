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

namespace Surfnet\StepupRa\RaBundle\Security\Authentication\Handler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string[]
     */
    private $logoutRedirectUrl;

    /**
     * @param TranslatorInterface $translator
     * @param string[] $locales
     * @param string[] $logoutRedirectUrl
     */
    public function __construct(TranslatorInterface $translator, array $locales, array $logoutRedirectUrl)
    {
        $this->translator = $translator;
        $this->logoutRedirectUrl = $logoutRedirectUrl;
    }

    public function onLogoutSuccess(Request $request)
    {
        return new RedirectResponse($this->logoutRedirectUrl[$this->translator->getLocale()]);
    }
}
