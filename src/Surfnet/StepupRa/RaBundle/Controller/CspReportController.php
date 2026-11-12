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

namespace Surfnet\StepupRa\RaBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CspReportController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $securityLogger,
    ) {
    }

    #[Route(
        path: '/csp/report',
        name: 'nelmio_security_report',
        methods: ['POST'],
    )]
    public function report(Request $request): Response
    {
        $this->securityLogger->warning('CSP violation report received', [
            'report' => json_decode($request->getContent(), true),
            'content_type' => $request->headers->get('Content-Type'),
        ]);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
