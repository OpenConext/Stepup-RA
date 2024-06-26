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

use JMS\TranslationBundle\Annotation\Ignore;
use Surfnet\StepupRa\RaBundle\Command\StartVettingProcedureCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StartVettingProcedureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('registrationCode', TextType::class, [
            'label' => /** @Ignore */ false,
            'required' => true,
            'attr' => [
                'autofocus' => true,
                'autocomplete' => 'off',
                'placeholder' => 'ra.form.start_vetting_procedure.enter_activation_code_here',
                'class' => 'fa-search',
            ]
        ]);
        $builder->add('search', SubmitType::class, [
            'label' => 'ra.form.start_vetting_procedure.search',
            'attr' => [ 'class' => 'btn btn-primary' ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StartVettingProcedureCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ra_start_vetting_procedure';
    }
}
