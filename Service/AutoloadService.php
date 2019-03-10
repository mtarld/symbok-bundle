<?php

namespace Mtarld\SymbokBundle\Service;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Mtarld\SymbokBundle\Autoload\Autoload;
use Mtarld\SymbokBundle\Cache\CacheInterface;
use Mtarld\SymbokBundle\Exception\SymbokException;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AutoloadService
{
    /** @var CacheInterface */
    private $cache;

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function register(string $namespace): void
    {
        if (empty($namespace)) {
            throw new SymbokException('Trying to register an empty namespace');
        }

        $classLoader = null;
        foreach (spl_autoload_functions() as $loader) {
            if (is_array($loader)) {
                if (is_a($loader[0], DebugClassLoader::class)) {
                    $loader = $loader[0]->getClassLoader();
                }
            } else {
                continue;
            }

            if (is_a($loader[0], ComposerClassLoader::class) && method_exists($loader[0], 'findFile')) {
                $classLoader = $loader[0];
            }
        }

        if (!$classLoader) {
            throw new \RuntimeException('Unable to find ' . ComposerClassLoader::class);
        }

        $loader = new Autoload($namespace, $classLoader, $this->cache, $this->container);

        spl_autoload_register([$loader, 'load'], true, true);
    }
}
