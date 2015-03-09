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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SearchRaSecondFactorsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.name',
        ]);
        $builder->add('type', 'choice', [
            'label' => 'ra.form.ra_search_ra_second_factors.label.type',
            'choices' => [
                'sms'     => 'ra.form.ra_search_ra_second_factors.choice.type.sms',
                'yubikey' => 'ra.form.ra_search_ra_second_factors.choice.type.yubikey',
            ],
            'required' => false,
        ]);
        $builder->add('secondFactorId', null, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.second_factor_id',
        ]);
        $builder->add('email', null, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.email',
        ]);
        $builder->add('status', 'choice', [
            'label' => 'ra.form.ra_search_ra_second_factors.label.status',
            'choices' => [
                'unverified' => 'ra.form.ra_search_ra_second_factors.choice.status.unverified',
                'verified'   => 'ra.form.ra_search_ra_second_factors.choice.status.verified',
                'vetted'     => 'ra.form.ra_search_ra_second_factors.choice.status.vetted',
                'revoked'    => 'ra.form.ra_search_ra_second_factors.choice.status.revoked',
            ],
            'required' => false,
        ]);
        $builder->add('search', 'submit', [
            'label' => 'ra.form.ra_search_ra_second_factors.button.search',
            'attr' => [ 'class' => 'btn btn-primary' ],
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Surfnet\StepupRa\RaBundle\Command\SearchRaSecondFactorsCommand',
        ]);
    }

    public function getName()
    {
        return 'ra_search_ra_second_factors';
    }
}
