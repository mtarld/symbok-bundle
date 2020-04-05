<?php

namespace Mtarld\SymbokBundle\DependencyInjection;

use Mtarld\SymbokBundle\Autoload\Autoload;
use Mtarld\SymbokBundle\Autoload\AutoloadFinder;
use Mtarld\SymbokBundle\Behavior\AllArgsConstructorBehavior;
use Mtarld\SymbokBundle\Behavior\GetterBehavior;
use Mtarld\SymbokBundle\Behavior\SetterBehavior;
use Mtarld\SymbokBundle\Command\PreviewCommand;
use Mtarld\SymbokBundle\Command\SavedUpdaterCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

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
        $loader->load('pass_config.xml');

        if (null === $configuration = $this->getConfiguration($configs, $container)) {
            return;
        }

        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'symbok',
            $config
        );

        $namespaces = $config['namespaces'];
        $defaults = $config['defaults'];

        $container->getDefinition(Autoload::class)->replaceArgument('$namespaces', $namespaces);
        $container->getDefinition(AutoloadFinder::class)->replaceArgument('$namespace', $namespaces[0] ?? '');
        $container->getDefinition(SavedUpdaterCommand::class)->replaceArgument('$namespaces', $namespaces);
        $container->getDefinition(PreviewCommand::class)->replaceArgument('$namespaces', $namespaces);
        $container->getDefinition(AllArgsConstructorBehavior::class)->replaceArgument('$defaults', $defaults['constructor'] ?? []);
        $container->getDefinition(GetterBehavior::class)->replaceArgument('$defaults', $defaults['getter'] ?? []);
        $container->getDefinition(SetterBehavior::class)->replaceArgument('$defaults', $defaults['setter'] ?? []);
    }
}
