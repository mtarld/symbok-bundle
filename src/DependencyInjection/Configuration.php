<?php

namespace Mtarld\SymbokBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UndefinedMethod
     * @psalm-suppress RedundantCondition
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('symbok');

        $rootNode = method_exists($builder, 'getRootNode') ? $builder->getRootNode() : $rootNode = $builder->root('symbok');
        $rootNode
            ->children()
                ->arrayNode('namespaces')
                    ->prototype('variable')->isRequired()->end()
                ->end()
                ->arrayNode('defaults')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('getter')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('nullable')->defaultValue(true)->treatNullLike(true)->end()
                            ->end()
                        ->end()
                        ->arrayNode('setter')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('fluent')->defaultValue(true)->treatNullLike(true)->end()
                                ->booleanNode('nullable')->defaultValue(true)->treatNullLike(true)->end()
                                ->booleanNode('updateOtherSide')->defaultValue(true)->treatNullLike(true)->end()
                            ->end()
                        ->end()
                        ->arrayNode('constructor')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('nullable')->defaultValue(true)->treatNullLike(true)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
