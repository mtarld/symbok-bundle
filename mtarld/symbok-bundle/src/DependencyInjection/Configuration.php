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
            ->arrayNode('namespaces')->prototype('variable')
            ->end()
            ->end()
            ->end();

        return $builder;
    }
}
