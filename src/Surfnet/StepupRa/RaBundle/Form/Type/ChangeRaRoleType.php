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

use Surfnet\StepupRa\RaBundle\Form\Extension\RaRoleChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangeRaRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', 'choice', [
                'label'       => 'ra.management.form.change_ra_role.label.role',
                'choice_list' => RaRoleChoiceList::createChoiceList()
            ])
            ->add('create_ra', 'submit', [
                'label' => 'ra.management.form.change_ra_role.label.save',
                'attr'  => ['class' => 'btn btn-primary pull-right change-ra-role']
            ])
            ->add('cancel', 'anchor', [
                'label' => 'ra.management.form.create_ra.label.cancel',
                'route' => 'ra_management_ra_candidate_search',
                'attr'  => ['class' => 'btn btn-link pull-right cancel']
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Surfnet\StepupRa\RaBundle\Command\ChangeRaRoleCommand'
        ]);
    }

    public function getName()
    {
        return 'ra_management_change_ra_role';
    }
}
