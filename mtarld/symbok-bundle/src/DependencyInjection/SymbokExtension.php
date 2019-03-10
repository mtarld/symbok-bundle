<?php

namespace Mtarld\SymbokBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class SymbokExtension extends ConfigurableExtension
{
    public function getAlias(): string
    {
        return 'symbok';
    }

    /**
     * Configures the passed container according to the merged configuration.
     *
     * @throws \Exception
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        foreach ($mergedConfig as $name => $value) {
            $container->setParameter(
                'symbok.' . $name,
                $value
            );
        }
    }
}
