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

namespace Surfnet\StepupRa\RaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RetractRegistrationAuthorityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create(
                'button-group',
                ButtonGroupType::class,
                [
                    'inherit_data' => true,
                ]
            )
            ->add('confirm', SubmitType::class, [
                'attr' => ['class' => 'btn btn-warning pull-right'],
                'label' => 'ra.management.retract_ra.modal.confirm',
            ])
            ->add('cancel', SubmitType::class, [
                'attr' => ['class' => 'btn btn-info pull-right'],
                'label' => 'ra.management.retract_ra.modal.cancel',
            ])
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Surfnet\StepupRa\RaBundle\Command\RetractRegistrationAuthorityCommand'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ra_management_retract_registration_authority';
    }
}
