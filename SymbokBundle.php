<?php

namespace Mtarld\SymbokBundle;

use Mtarld\SymbokBundle\Cache\NoCache;
use Mtarld\SymbokBundle\Cache\PhpFileCache;
use Mtarld\SymbokBundle\DependencyInjection\SymbokExtension;
use Mtarld\SymbokBundle\Service\AutoloadService;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SymbokBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SymbokExtension();
    }

    public function boot()
    {
        $parameters = $this->container->getParameter('symbok');
        $namespaces = $parameters['namespaces'];
        $cacheEnabled = $parameters['cache'];

        if ($cacheEnabled) {
            $cacheDir = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR;
            $fileCache = new PhpFileCache("{$cacheDir}symbok");
        } else {
            $fileCache = new NoCache();
        }

        /** @var AutoloadService $autoload */
        $autoload = $this->container->get(AutoloadService::class);
        $autoload->setCache($fileCache);
        foreach ($namespaces as $namespace) {
            $autoload->register($namespace);
        }
    }
}
