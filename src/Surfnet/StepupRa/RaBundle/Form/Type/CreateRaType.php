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

use Surfnet\StepupRa\RaBundle\Command\AccreditCandidateCommand;
use Surfnet\StepupRa\RaBundle\Form\Extension\RaRoleChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateRaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options = array_combine(
            $builder->getData()->availableInstitutions,
            $builder->getData()->availableInstitutions
        );

        $builder
            ->add('location', TextareaType::class, [
                'label' => 'ra.management.form.create_ra.label.location'
            ])
            ->add('contactInformation', TextareaType::class, [
                'label' => 'ra.management.form.create_ra.label.contact_information'
            ])->add('roleAtInstitution', RoleAtInstitutionType::class, [
                'choices' => $options,
                'required' => true,
                'label' => 'ra.management.form.create_ra.label.role',
            ])
            ->add('create_ra', SubmitType::class, [
                'label' => 'ra.management.form.create_ra.label.create_ra',
                'attr' => ['class' => 'btn btn-primary pull-right create-ra']
            ])
            ->add('cancel', AnchorType::class, [
                'label' => 'ra.management.form.create_ra.label.cancel',
                'route' => 'ra_management_ra_candidate_search',
                'attr'  => ['class' => 'btn btn-link pull-right cancel']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccreditCandidateCommand::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ra_management_create_ra';
    }
}
