<?php

namespace Mtarld\SymbokBundle;

use Mtarld\SymbokBundle\Autoload\Autoload;
use Mtarld\SymbokBundle\DependencyInjection\SymbokExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @codeCoverageIgnore
 */
class SymbokBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SymbokExtension();
    }

    public function boot()
    {
        $this->container->get(Autoload::class)->register();
    }
}
