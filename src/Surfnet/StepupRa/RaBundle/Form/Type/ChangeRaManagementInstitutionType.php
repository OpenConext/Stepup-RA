<?php

/**
 * Copyright 2018 SURFnet B.V.
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

use Surfnet\StepupRa\RaBundle\Command\ChangeRaManagementInstitutionCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeRaManagementInstitutionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options = array_combine(
            $builder->getData()->availableInstitutions,
            $builder->getData()->availableInstitutions
        );

        $builder
            ->add('raaManagementInstitution', ChoiceType::class, [
                'label'       => 'ra.management.form.change_ra_management_institution.label.institution',
                'choices' => $options
            ])->add('search', SubmitType::class, [
                'label' => 'ra.management.form.change_ra_management_institution.label.submit',
                'attr'  => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChangeRaManagementInstitutionCommand::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'change_ra_management_institution';
    }
}
