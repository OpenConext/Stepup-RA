<?php

/**
 * Copyright 2022 SURFnet bv
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

use Surfnet\StepupRa\RaBundle\Command\SearchRecoveryTokensCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchRecoveryTokensType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, [
            'label' => 'ra.form.ra_search_recovery_tokens.label.name',
        ]);
        $builder->add('type', ChoiceType::class, [
            'label' => 'ra.form.ra_search_recovery_tokens.label.type',
            'choices' => [
                'sms' => 'sms',
                'safe-store' => 'safe-store',
            ],
            'required' => false,
        ]);

        $builder->add('email', null, [
            'label' => 'ra.form.ra_search_recovery_tokens.label.email',
        ]);

        /** @var SearchRecoveryTokensCommand $data */
        $data = $builder->getData();

        $builder->add('status', ChoiceType::class, [
            'label' => 'ra.form.ra_search_ra_second_factors.label.status',
            'choices' => [
                'ra.form.ra_search_recovery_tokens.choice.status.active' => 'active',
                'ra.form.ra_search_recovery_tokens.choice.status.revoked' => 'revoked',
                'ra.form.ra_search_recovery_tokens.choice.status.forgotten' => 'forgotten',
            ],
            'required' => false,
        ]);

        $builder->add('institution', ChoiceType::class, [
            'label' => 'ra.form.ra_search_recovery_tokens.label.institution',
            'choices' => $data->institutionFilterOptions,
            'required' => false,
        ]);

        $buttonGroup = $builder->create(
            'button-group',
            ButtonGroupType::class,
            [
                'inherit_data' => true,
            ],
        )
        ->add('search', SubmitType::class, [
            'label' => 'ra.form.ra_search_recovery_tokens.button.search',
            'attr' => [ 'class' => 'btn btn-primary' ],
        ]);

        $builder->add($buttonGroup);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchRecoveryTokensCommand::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ra_search_recovery_tokens';
    }
}
