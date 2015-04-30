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

use Surfnet\StepupRa\RaBundle\Form\Extension\InstitutionListingChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstitutionSelectionType extends AbstractType
{
    private $institutionListingChoiceList;

    public function __construct(InstitutionListingChoiceList $institutionListingChoiceList)
    {
        $this->institutionListingChoiceList = $institutionListingChoiceList;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('institution', 'choice', [
                'choice_list' => $this->institutionListingChoiceList->create(),
                'label' => 'ra.form.ra_select_institution.label.institution',
            ])
            ->add('select_and_apply', 'submit', [
                'label' => 'ra.form.ra_select_institution.button.select_and_apply',
                'attr'  => ['class' => 'btn btn-primary pull-right'],
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Surfnet\StepupRa\RaBundle\Command\SelectInstitutionCommand'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sraa_institution_select';
    }
}
