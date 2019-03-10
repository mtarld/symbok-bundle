<?php

namespace Mtarld\SymbokBundle;

use Mtarld\SymbokBundle\Autoload\Autoload as SymbokAutoload;
use Mtarld\SymbokBundle\Cache\Impl\PhpFileCache;
use Mtarld\SymbokBundle\DependencyInjection\SymbokExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SymbokBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new SymbokExtension();
    }

    public function boot()
    {
        $cacheDir = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR;
        $fileCache = new PhpFileCache("{$cacheDir}symbok");

        foreach ($this->container->getParameter('symbok.namespaces') as $namespace) {
            SymbokAutoload::register($namespace, $fileCache);
        }
    }
}
