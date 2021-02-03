<?php

/**
 * Copyright 2019 SURFnet B.V.
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
use Surfnet\StepupRa\RaBundle\Value\RoleAtInstitution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * RoleAtInstitutionType
 *
 * The RoleAtInstitutionType can be used to render a compound form component that consists of a role and an institution
 * form field. These are used in conjunction to describe a role of an identity at an insitution.
 *
 * Use the 'required' option to set the select fields to be either optional (for search) or mandatory.
 * Use the 'choices' option to populate the intitutions field, the roles select list is filled with a RaRoleChoiceList
 *
 * This type can be use for example:
 *  - search forms when you want to find identities with a specific role at a given institution
 *  - when roles are applied to identities
 *
 */
class RoleAtInstitutionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $selectRaaOptions = $options['choices'];
        $isRequired = $options['required'];

        $builder ->add('role', ChoiceType::class, [
            'label' => false,
            'choices' => RaRoleChoiceList::create(),
            'choice_value' =>
            /**
             * @param mixed $choice
             * @return mixed
             */
            function ($choice) {
                return $choice;
            },
            'required' => $isRequired,
        ])->add('institution', ChoiceType::class, [
            'label' => 'ra.form.role_at_institution.label.institution',
            'choices' => $selectRaaOptions,
            'required' => $isRequired,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => RoleAtInstitution::class,
            'choices' => [],
            'horizontal' => true,
            'error_bubbling' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'ra_role_at_institution';
    }
}
