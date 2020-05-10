<?php

namespace Mtarld\SymbokBundle;

use Mtarld\SymbokBundle\Autoload\Autoload;
use Mtarld\SymbokBundle\DependencyInjection\SymbokExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @final
 */
class SymbokBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new SymbokExtension();
    }

    public function boot(): void
    {
        /** @var Autoload $autoload */
        $autoload = $this->container->get('symbok.autoload');
        $autoload->register();
    }
}
