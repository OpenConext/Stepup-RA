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
        $loader->load('security.yml');

        // inject the required loa as parameter into the service container
        $container->setParameter('surfnet_stepup_ra.security.required_loa', $config['required_loa']);

        $gssfSecondFactors = array_keys($config['enabled_generic_second_factors']);
        $container->setParameter(
            'surfnet_stepup_ra.enabled_second_factors',
            array_merge($config['enabled_second_factors'], $gssfSecondFactors),
        );

        $container->setParameter(
            'ra.security.authentication.session.maximum_absolute_lifetime_in_seconds',
            $config['session_lifetimes']['max_absolute_lifetime'],
        );
        $container->setParameter(
            'ra.security.authentication.session.maximum_relative_lifetime_in_seconds',
            $config['session_lifetimes']['max_relative_lifetime'],
        );

        $container->setParameter('surfnet_stepup_ra.self_service_url', $config['self_service_url']);
    }
}
