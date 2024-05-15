<?php

/**
 * Copyright 2015 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ButtonTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnchorType extends AbstractType implements ButtonTypeInterface
{
    public function getParent(): string
    {
        return ButtonType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'anchor';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'route'            => null,
            'route_parameters' => [],
            'auto_initialize'  => false,
        ]);

        $resolver->setRequired(['route']);

        $resolver->setAllowedTypes('route', 'string');
        $resolver->setAllowedTypes('route_parameters', 'array');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['route'] = $options['route'];
        $view->vars['routeParameters'] = $options['route_parameters'];
    }
}
