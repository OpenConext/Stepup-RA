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

use Surfnet\StepupRa\RaBundle\Command\ChangeRaRoleCommand;
use Surfnet\StepupRa\RaBundle\Form\Extension\RaRoleChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeRaRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', ChoiceType::class, [
                'label'       => 'ra.management.form.change_ra_role.label.role',
                'choices' => RaRoleChoiceList::create(),
                'choice_value' => fn($choice) => $choice,
            ])

            ->add(
                $builder->create(
                    'button-group',
                    ButtonGroupType::class,
                    [
                        'inherit_data' => true,
                    ],
                )
                ->add('create_ra', SubmitType::class, [
                    'label' => 'ra.management.form.change_ra_role.label.save',
                    'attr'  => ['class' => 'btn btn-primary']
                ])
                ->add('cancel', AnchorType::class, [
                    'label' => 'ra.management.form.create_ra.label.cancel',
                    'route' => 'ra_management_ra_candidate_search',
                    'attr'  => ['class' => 'btn btn-link']
                ]),
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangeRaRoleCommand::class
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ra_management_change_ra_role';
    }
}
