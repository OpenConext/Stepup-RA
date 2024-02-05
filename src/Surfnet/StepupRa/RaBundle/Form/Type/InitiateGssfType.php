<?php

/**
 * Copyright 2015 SURFnet B.V.
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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class InitiateGssfType extends AbstractType
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $action = $this->router->generate(
            'ra_vetting_gssf_authenticate',
            ['procedureId' => $options['procedureId'], 'provider' => $options['provider']],
        );

        $builder
            ->add('submit', SubmitType::class, [
                'attr'  => ['class' => 'btn btn-primary'],
                /** @Ignore */
                'label' => $options['label']
            ])
            ->setAction($action);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['procedureId', 'provider']);
        $resolver->setAllowedTypes('procedureId', 'string');
        $resolver->setAllowedTypes('provider', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'ra_initiate_gssf';
    }
}
