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

namespace Surfnet\StepupRa\SamlStepupProviderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('surfnet_stepup_ra_saml_stepup_provider');

        $this->addRoutesSection($rootNode);
        $this->addProvidersSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addRoutesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
            ->arrayNode('routes')
                ->children()
                    ->scalarNode('consume_assertion')
                        ->isRequired()
                        ->validate()
                            ->ifTrue(function ($v) {
                                return !is_string($v) || strlen($v) === 0;
                            })
                            ->thenInvalid('Consume assertion route must be a non-empty string')
                        ->end()
                    ->end()
                    ->scalarNode('metadata')
                        ->isRequired()
                        ->validate()
                            ->ifTrue(function ($v) {
                                return !is_string($v) || strlen($v) === 0;
                            })
                            ->thenInvalid('Metadata route must be a non-empty string')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addProvidersSection(ArrayNodeDefinition $rootNode)
    {
        /** @var ArrayNodeDefinition $protoType */
        $protoType = $rootNode
            ->children()
                ->arrayNode('providers')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('type')
                ->prototype('array');

        $protoType
            ->children()
                ->arrayNode('hosted')
                    ->children()
                        ->arrayNode('service_provider')
                            ->children()
                                ->scalarNode('public_key')
                                    ->isRequired()
                                    ->info('The absolute path to the public key used to sign AuthnRequests')
                                ->end()
                                ->scalarNode('private_key')
                                    ->isRequired()
                                    ->info('The absolute path to the private key used to sign AuthnRequests')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('metadata')
                            ->children()
                                ->scalarNode('public_key')
                                    ->isRequired()
                                    ->info('The absolute path to the public key used to sign the metadata')
                                ->end()
                                ->scalarNode('private_key')
                                    ->isRequired()
                                    ->info('The absolute path to the private key used to sign the metadata')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('remote')
                    ->children()
                        ->scalarNode('entity_id')
                            ->isRequired()
                            ->info('The EntityID of the remote identity provider')
                        ->end()
                        ->scalarNode('sso_url')
                            ->isRequired()
                            ->info('The name of the route to generate the SSO URL')
                        ->end()
                        ->scalarNode('certificate')
                            ->isRequired()
                            ->info(
                                'The contents of the certificate used to sign the AuthnResponse with, if different from'
                                . ' the public key configured below'
                            )
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('view_config')
                    ->children()
                        ->arrayNode('page_title')
                            ->children()
                                ->scalarNode('en_GB')
                                    ->isRequired()
                                    ->info('English page title translation')
                                ->end()
                                ->scalarNode('nl_NL')
                                    ->isRequired()
                                    ->info('Dutch alt page title translation')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('explanation')
                            ->children()
                                ->scalarNode('en_GB')
                                    ->isRequired()
                                    ->info('English explanation translation')
                                ->end()
                                ->scalarNode('nl_NL')
                                    ->isRequired()
                                    ->info('Dutch explanation translation')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('initiate')
                            ->children()
                                ->scalarNode('en_GB')
                                    ->isRequired()
                                    ->info('English initiate text translation')
                                ->end()
                                ->scalarNode('nl_NL')
                                    ->isRequired()
                                    ->info('Dutch initiate text translation')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('gssf_id_mismatch')
                            ->children()
                                ->scalarNode('en_GB')
                                    ->isRequired()
                                    ->info('English id mismatch text translation')
                                ->end()
                                ->scalarNode('nl_NL')
                                    ->isRequired()
                                    ->info('Dutch id mismatch text translation')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
