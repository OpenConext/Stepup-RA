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

namespace Surfnet\StepupRa\RaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SurfnetStepupRaRaExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $gatewayGuzzleOptions = [
            'base_url' => $config['gateway_api']['url'],
            'defaults' => [
                'auth' => [
                    $config['gateway_api']['credentials']['username'],
                    $config['gateway_api']['credentials']['password'],
                    'basic'
                ],
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]
        ];

        $gatewayGuzzle = $container->getDefinition('ra.guzzle.gateway_api');
        $gatewayGuzzle->replaceArgument(0, $gatewayGuzzleOptions);

        $smsSecondFactorService =
            $container->getDefinition('ra.service.sms_second_factor');
        $smsSecondFactorService->replaceArgument(3, $config['sms']['originator']);

        $container
            ->getDefinition('ra.service.challenge_handler')
            ->replaceArgument(2, $config['sms']['otp_expiry_interval'])
            ->replaceArgument(3, $config['sms']['maximum_otp_requests']);

        // inject the required loa as parameter into the service container
        $container->setParameter('surfnet_stepup_ra.security.required_loa', $config['required_loa']);
    }
}
