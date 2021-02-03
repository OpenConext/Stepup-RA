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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VerifyPhoneNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('challenge', TextType::class, [
            'label' => 'ra.form.ra_verify_phone_number.text.challenge',
            'required' => true,
            'attr' => array(
                'autofocus' => true,
            ),
        ]);
        $builder->add('verifyChallenge', SubmitType::class, [
            'label' => 'ra.form.ra_verify_phone_number.button.verify_challenge',
            'attr' => [ 'class' => 'btn btn-primary pull-right' ],
        ]);
        $builder->add('resendChallenge', AnchorType::class, [
            'label' => 'ra.form.ra_verify_phone_number.button.resend_challenge',
            'attr' => [ 'class' => 'btn btn-default' ],
            'route' => 'ra_vetting_sms_send_challenge',
            'route_parameters' => ['procedureId' => $options['procedureId']],
        ]);
        $builder->add('cancel', SubmitType::class, [
            'label' => 'ra.vetting.button.cancel_procedure',
            'attr' => [ 'class' => 'btn btn-danger' ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'Surfnet\StepupBundle\Command\VerifyPossessionOfPhoneCommand',
            'procedureId' => null,
        ]);

        $resolver->setRequired(['procedureId']);

        $resolver->setAllowedTypes('procedureId', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'ra_verify_phone_number';
    }
}
