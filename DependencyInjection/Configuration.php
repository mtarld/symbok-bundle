<?php

namespace Mtarld\SymbokBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $builder
            ->root('symbok')
                ->children()
                    ->arrayNode('namespaces')
                        ->prototype('variable')->isRequired()->end()
                    ->end()
                    ->arrayNode('defaults')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('nullable')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('constructor')->defaultValue(true)->treatNullLike(true)->end()
                                    ->booleanNode('getter_setter')->defaultValue(false)->treatNullLike(false)->end()
                                ->end()
                            ->end()
                            ->booleanNode('fluent_setters')->defaultValue(false)->treatNullLike(false)->end()
                        ->end()
                    ->end()
                ->end();

        return $builder;
    }
}
