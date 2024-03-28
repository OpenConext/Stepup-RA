<?php

declare(strict_types=1);

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\StepupRa\RaBundle\Controller\Vetting\Gssf;

use JMS\TranslationBundle\Annotation\Ignore;
use Surfnet\StepupBundle\Value\Provider\ViewConfigCollection;
use Surfnet\StepupRa\RaBundle\Form\Type\InitiateGssfType;
use Surfnet\StepupRa\SamlStepupProviderBundle\Provider\ViewConfig;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class GssfInitiateFormService
{
    public function __construct(
        private readonly ViewConfigCollection $collection,
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment          $twig,
    ) {
    }

    public function renderInitiateForm(string $procedureId, string $providerName, array $parameters = []): Response
    {
        $secondFactorConfig = $this->collection->getByIdentifier($providerName);
        assert($secondFactorConfig instanceof ViewConfig);

        $form = $this->formFactory->create(
            InitiateGssfType::class,
            null,
            [
                'procedureId' => $procedureId,
                'provider' => $providerName,
                /** @Ignore from translation message extraction */
                'label' => $secondFactorConfig->getInitiate()
            ],
        );

        $templateParameters = array_merge(
            $parameters,
            [
                'form' => $form->createView(),
                'procedureId' => $procedureId,
                'provider' => $providerName,
                'secondFactorConfig' => $secondFactorConfig
            ],
        );

        return new Response(
            $this->twig->render(
                '@default/vetting/gssf/initiate.html.twig',
                $templateParameters,
            ),
        );
    }
}
