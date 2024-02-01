<?php

/**
 * Copyright 2014 SURFnet bv
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

use Surfnet\SamlBundle\Http\PostBinding;
use Surfnet\SamlBundle\Http\XMLResponse;
use Surfnet\SamlBundle\Metadata\MetadataFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SamlController extends AbstractController
{
    public function consumeAssertion(Request $httpRequest): Response
    {
        /** @var PostBinding $postBinding */
        $postBinding = $this->container->get('surfnet_saml.http.post_binding');

        $assertion = $postBinding->processResponse(
            $httpRequest,
            $this->container->get('surfnet_saml.remote.idp'),
            $this->container->get('surfnet_saml.hosted.service_provider'),
        );
        return $this->render('saml/consume_assertion.html.twig',
            ['assertion' => $assertion]);
    }

    public function metadata(): XMLResponse
    {
        /** @var MetadataFactory $metadataFactory */
        $metadataFactory = $this->container->get('surfnet_saml.metadata_factory');

        return new XMLResponse($metadataFactory->generate());
    }
}
