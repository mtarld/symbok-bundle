<?php

namespace Mtarld\SymbokBundle\Cache\Impl;

use Mtarld\SymbokBundle\Cache\CacheInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class PhpFileCache implements CacheInterface
{
    /** @var PhpFilesAdapter */
    private $cacheAdapter;

    public function __construct(string $directory)
    {
        $this->cacheAdapter = new PhpFilesAdapter('cache.app', 0, $directory);
    }

    public function exists(string $className): bool
    {
        $cacheFilename = $this->getCacheFilename($className);

        return $this->cacheAdapter->hasItem($cacheFilename);
    }

    private function getCacheFilename(string $className): string
    {
        return str_replace('\\', '.', $className) . '.php';
    }

    public function load(string $className): void
    {
        $cacheFilename = $this->getCacheFilename($className);
        $cachedFile = $this->cacheAdapter->getItem($cacheFilename);

        eval($cachedFile->get());
    }

    public function write(string $className, string $content): void
    {
        $cacheFilename = $this->getCacheFilename($className);
        $cachedFile = $this->cacheAdapter->getItem($cacheFilename);
        $cachedFile->set($content);
        print_r($content);
        // $this->cacheAdapter->save($cachedFile);
    }
}
