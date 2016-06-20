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
use Symfony\Component\OptionsResolver\OptionsResolver;

class VerifyIdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('documentNumber', 'text', [
            'label' => 'ra.form.verify_identity.document_number.label',
            'horizontal_label_class' => 'col-sm-6 left-aligned',
            'horizontal_input_wrapper_class' => 'col-sm-6',
            'attr' => [
                'autofocus' => true,
                'autocomplete' => 'off',
            ]
        ]);
        $builder->add('identityVerified', 'checkbox', [
            'label' => 'ra.form.verify_identity.identity_verified.label',
            'widget_checkbox_label' => 'widget',
            'widget_form_group_attr' => ['class' => 'form-group form-group-verify-identity'],
        ]);
        $builder->add('verifyIdentity', 'submit', [
            'label' => 'ra.form.verify_identity.verify_identity.button',
            'attr' => [ 'class' => 'btn btn-primary pull-right' ],
        ]);
        $builder->add('cancel', 'submit', [
            'label' => 'ra.vetting.button.cancel_procedure',
            'attr' => [ 'class' => 'btn btn-danger' ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Surfnet\StepupRa\RaBundle\Command\VerifyIdentityCommand',
        ]);
    }

    public function getName()
    {
        return 'ra_verify_identity';
    }
}
