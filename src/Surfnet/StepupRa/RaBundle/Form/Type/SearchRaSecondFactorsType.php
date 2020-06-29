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

use Surfnet\StepupRa\RaBundle\Command\SearchRaSecondFactorsCommand;
use Surfnet\StepupRa\RaBundle\Form\Extension\SecondFactorTypeChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchRaSecondFactorsType extends AbstractType
{
    /**
     * @var SecondFactorTypeChoiceList
     */
    private $secondFactorTypeChoiseList;

    public function __construct(SecondFactorTypeChoiceList $secondFactorTypeChoiceList)
    {
        $this->secondFactorTypeChoiseList = $secondFactorTypeChoiceList;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.name',
        ]);
        $builder->add('type', ChoiceType::class, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.type',
            'choices' => $this->secondFactorTypeChoiseList->create(),
            'required' => false,
        ]);
        $builder->add('secondFactorId', null, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.second_factor_id',
        ]);
        $builder->add('email', null, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.email',
        ]);
        $builder->add('status', ChoiceType::class, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.status',
            'choices' => [
                'ra.form.ra_search_ra_second_factors.choice.status.unverified' => 'unverified',
                'ra.form.ra_search_ra_second_factors.choice.status.verified' => 'verified',
                'ra.form.ra_search_ra_second_factors.choice.status.vetted' => 'vetted',
                'ra.form.ra_search_ra_second_factors.choice.status.revoked' => 'revoked',
            ],
            'required' => false,
        ]);

        /** @var SearchRaSecondFactorsCommand $data */
        $data = $builder->getData();

        $builder->add('institution', ChoiceType::class, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.institution',
            'choices' => $data->institutionFilterOptions,
            'required' => false,
        ]);

        $buttonGroup = $builder->create(
            'button-group',
            ButtonGroupType::class,
            [
                'inherit_data' => true,
            ]
        )
            ->add('search', SubmitType::class, [
                'label' => 'ra.form.ra_search_ra_second_factors.button.search',
                'attr' => [ 'class' => 'btn btn-primary' ],
            ]);

        if ($options['enable_export_button']) {
            $buttonGroup->add('export', SubmitType::class, [
                'label' => 'ra.form.ra_search_ra_second_factors.button.export',
                'attr' => ['class' => 'btn btn-secondary'],
            ]);
        }

        $builder->add($buttonGroup);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Surfnet\StepupRa\RaBundle\Command\SearchRaSecondFactorsCommand',
            'enable_export_button' => true,
        ]);

        $resolver->setAllowedTypes('enable_export_button', 'bool');
    }

    public function getBlockPrefix()
    {
        return 'ra_search_ra_second_factors';
    }
}
