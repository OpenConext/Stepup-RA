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

use Surfnet\StepupRa\RaBundle\Command\SearchRaListingCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchRaListingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SearchRaListingCommand $data */
        $data = $builder->getData();

        $builder
            ->add('name', null, [
                'label' => 'ra.form.ra_search_ra_listing.label.name',
            ])
            ->add('email', null, [
                'label' => 'ra.form.ra_search_ra_listing.label.email',
            ])->add('institution', ChoiceType::class, [
                'label' => 'ra.form.ra_search_ra_listing.label.institution',
                'choices' => $data->institutionFilterOptions,
                'required' => false,
            ])->add('roleAtInstitution', RoleAtInstitutionType::class, [
                'choices' => $data->raInstitutionFilterOptions,
                'label' => 'ra.form.ra_search_ra_listing.label.role',
                'required' => false,
            ])->add('search', SubmitType::class, [
                'label' => 'ra.form.ra_search_ra_listing.button.search',
                'attr'  => ['class' => 'btn btn-primary search-button'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchRaListingCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ra_search_ra_listing';
    }
}
