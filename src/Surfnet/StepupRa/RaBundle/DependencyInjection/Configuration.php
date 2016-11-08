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

use Surfnet\StepupBundle\Exception\DomainException;
use Surfnet\StepupBundle\Exception\InvalidArgumentException;
use Surfnet\StepupBundle\Value\SecondFactorType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('surfnet_stepup_ra_ra');

        $childNodes = $rootNode->children();
        $this->appendLoaConfiguration($childNodes);
        $this->appendSecondFactorTypesConfiguration($childNodes);
        $this->appendSessionConfiguration($childNodes);
        $this->appendUrlConfiguration($childNodes);

        return $treeBuilder;
    }

    private function appendLoaConfiguration(NodeBuilder $childNodes)
    {
        $childNodes
            ->scalarNode('required_loa')
                ->info('The required LOA to be able to log in, should match the loa defined at the gateway')
                ->isRequired()
                    ->validate()
                        ->ifTrue(function ($value) {
                            return !is_string($value);
                        })
                        ->thenInvalid('the required loa must be a string')
                    ->end()
            ->end();
    }

    /**
     * @param NodeBuilder $childNodes
     */
    private function appendSecondFactorTypesConfiguration(NodeBuilder $childNodes)
    {
        $childNodes

            ->arrayNode('enabled_second_factors')
                ->isRequired()
                ->prototype('scalar')
                    ->validate()
                        ->ifTrue(
                            function ($type) {
                                try {
                                    new SecondFactorType($type);
                                } catch (InvalidArgumentException $e) {
                                    return true;
                                } catch (DomainException $e) {
                                    return true;
                                }
                            }
                        )
                        ->thenInvalid(
                            'Enabled second factor type "%s" is not one of the valid types. See SecondFactorType'
                        )
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * @param NodeBuilder $childNodes
     */
    private function appendSessionConfiguration(NodeBuilder $childNodes)
    {
        $childNodes
            ->arrayNode('session_lifetimes')
                ->isRequired()
                ->children()
                    ->integerNode('max_absolute_lifetime')
                        ->isRequired()
                        ->defaultValue(3600)
                        ->info('The maximum lifetime of a session regardless of interaction by the user, in seconds.')
                        ->example('3600 -> 1 hour * 60 minutes * 60 seconds')
                        ->validate()
                            ->ifTrue(
                                function ($lifetime) {
                                    return !is_int($lifetime);
                                }
                            )
                            ->thenInvalid('max_absolute_lifetime must be an integer')
                        ->end()
                    ->end()
                    ->integerNode('max_relative_lifetime')
                        ->isRequired()
                        ->defaultValue(600)
                        ->info(
                            'The maximum relative lifetime of a session; the maximum allowed time between two '
                            . 'interactions by the user'
                        )
                        ->example('600 -> 10 minutes * 60 seconds')
                        ->validate()
                            ->ifTrue(
                                function ($lifetime) {
                                    return !is_int($lifetime);
                                }
                            )
                            ->thenInvalid('max_relative_lifetime must be an integer')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function appendUrlConfiguration(NodeBuilder $childNodes)
    {
        $childNodes
            ->scalarNode('self_service_url')
                ->info('The URL of Self Service, where a user can register and revoke second factors')
                ->validate()
                    ->ifTrue(
                        function ($url) {
                            return filter_var($url, FILTER_VALIDATE_URL) === false;
                        }
                    )
                    ->thenInvalid('self_service_url must be a valid url')
            ->end();
    }
}
