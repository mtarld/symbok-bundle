<?php

namespace Mtarld\SymbokBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @internal
 * @final
 */
class SymbokExtension extends Extension
{
    public function getAlias(): string
    {
        return 'symbok';
    }

    /**
     * @param array<array-key, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');
        $loader->load('compiler.xml');

        if (null === $configuration = $this->getConfiguration($configs, $container)) {
            return;
        }

        $config = $this->processConfiguration($configuration, $configs);

        $namespaces = $config['namespaces'];
        $container->setParameter('symbok.namespaces', $namespaces);

        $defaults = $config['defaults'];
        $container->setParameter('symbok.defaults.getter', $defaults['getter']);
        $container->setParameter('symbok.defaults.setter', $defaults['setter']);
        $container->setParameter('symbok.defaults.constructor', $defaults['constructor']);

        $container->getDefinition('symbok.autoload.autoload_finder')->setArgument(0, $namespaces[0] ?? '');
    }
}
