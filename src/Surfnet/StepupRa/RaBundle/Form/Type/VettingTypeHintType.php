<?php

/**
 * Copyright 2022 SURFnet B.V.
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

use Surfnet\StepupRa\RaBundle\Command\VettingTypeHintCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VettingTypeHintType extends AbstractType
{
    public const HINT_TEXTAREA_NAME_PREFIX = 'vetting_type_hint_';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($builder->getData()->locales as $locale) {
            $builder
                ->add(
                    self::HINT_TEXTAREA_NAME_PREFIX . $locale,
                    TextareaType::class,
                    [
                        'label' => $locale,
                    ]
                );
        }
        $builder
            ->add('institution',
                HiddenType::class
            )
            ->add(
                'continue',
                SubmitType::class,
                [
                    'label' => 'ra.form.vetting_type_hint.button.continue',
                    'attr' => ['class' => 'btn btn-primary pull-right'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => VettingTypeHintCommand::class,
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'vetting_type_hint';
    }
}
