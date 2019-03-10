<?php

namespace Mtarld\SymbokBundle\Autoload;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Mtarld\SymbokBundle\Cache\CacheInterface as Cache;
use Mtarld\SymbokBundle\Service\CompilerService;
use Mtarld\SymbokBundle\Service\TagsUpdaterService;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Autoload
{
    /** @var string */
    private $namespace;

    /** @var ComposerClassLoader */
    private $classLoader;

    /** @var CompilerService */
    private $compilerService;

    /** @var TagsUpdaterService */
    private $tagsUpdaterService;

    /** @var Cache */
    private $cache;

    /** @var Standard */
    private $serializer;

    /** @var ContainerInterface */
    private $container;

    public function __construct(
        string $namespace,
        ComposerClassLoader $classLoader,
        Cache $cache,
        ContainerInterface $container
    ) {
        $this->namespace = $namespace;
        $this->classLoader = $classLoader;
        $this->cache = $cache;
        $this->container = $container;
        $this->compilerService = $this->container->get(CompilerService::class);
        $this->compilerService->setClassLoader($classLoader);
        $this->tagsUpdaterService = $this->container->get(TagsUpdaterService::class);
        $this->serializer = new Standard();
    }

    public function load(string $class): void
    {
        if (substr($class, 0, strlen($this->namespace)) === $this->namespace) {
            $filename = $this->classLoader->findFile($class);
            if (file_exists($filename)) {
                if ($this->cache->exists($class)) {
                    $this->cache->load($class);
                } else {
                    $nodes = $this->compilerService->compile($filename);
                    if (sizeof($nodes)) {
                        $this->tagsUpdaterService->updateTags($filename, ...$nodes);
                        $this->cache->write($class, $this->serializer->prettyPrint($nodes));
                        $this->cache->load($class);
                    }
                }
            }
        }
    }
}
