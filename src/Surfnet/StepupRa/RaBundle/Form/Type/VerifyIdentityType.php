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

use Surfnet\StepupRa\RaBundle\Command\VerifyIdentityCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VerifyIdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('documentNumber', TextType::class, [
            'label' => 'ra.form.verify_identity.document_number.label',
            'attr' => [
                'autofocus' => true,
                'autocomplete' => 'off',
                'maxlength' => 6,
                'novalidate' => true,
            ]
        ]);
        $builder->add('identityVerified', CheckboxType::class, [
            'label' => 'ra.form.verify_identity.identity_verified.label',
        ]);
        $builder->add('verifyIdentity', SubmitType::class, [
            'label' => 'ra.form.verify_identity.verify_identity.button',
            'attr' => [ 'class' => 'btn btn-primary pull-right' ],
        ]);
        $builder->add('cancel', SubmitType::class, [
            'label' => 'ra.vetting.button.cancel_procedure',
            'attr' => [ 'class' => 'btn btn-danger' ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VerifyIdentityCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ra_verify_identity';
    }
}
