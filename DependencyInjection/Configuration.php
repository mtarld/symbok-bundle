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
            ->children()
            ->arrayNode('nullable')
            ->children()
            ->booleanNode('constructor')->isRequired()->treatNullLike(true)->end()
            ->booleanNode('getter_setter')->isRequired()->treatNullLike(false)->end()
            ->end()
            ->end()
            ->booleanNode('fluent_setters')->isRequired()->treatNullLike(false)->end()
            ->end()
            ->end();

        return $builder;
    }
}
