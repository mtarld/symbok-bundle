<?php

namespace Mtarld\SymbokBundle\Cache;

use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * @internal
 * @final
 */
class RuntimeClassCache
{
    private $cacheDir;
    private $debug;

    public function __construct(string $cacheDir, bool $debug)
    {
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;
    }

    public function cache(string $classFqcn, string $classPath, ReplacerInterface $replacer): ConfigCacheInterface
    {
        return (new ConfigCacheFactory($this->debug))->cache(
            sprintf('%s/%s.php', $this->cacheDir, str_replace('\\', '/', $classFqcn)),
            static function (ConfigCacheInterface $cache) use ($classPath, $classFqcn, $replacer) {
                $cache->write($replacer->replace($classFqcn), [new FileResource($classPath)]);
            }
        );
    }

    public function store(string $classFqcn, string $classPath, ReplacerInterface $replacer): void
    {
        $cache = new ConfigCache(
            sprintf('%s/%s.php', $this->cacheDir, str_replace('\\', '/', $classFqcn)),
            $this->debug
        );

        $cache->write($replacer->replace($classFqcn), [new FileResource($classPath)]);
    }
}
