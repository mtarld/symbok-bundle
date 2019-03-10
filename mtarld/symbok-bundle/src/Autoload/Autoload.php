<?php

namespace Mtarld\SymbokBundle\Autoload;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Mtarld\SymbokBundle\Cache\CacheInterface as Cache;
use Mtarld\SymbokBundle\Cache\Impl\NoCache;
use Mtarld\SymbokBundle\Compiler\Compiler\FileCompiler;
use Mtarld\SymbokBundle\Exception\SymbokException;
use Mtarld\SymbokBundle\Tags\Updater as TagsUpdater;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Debug\DebugClassLoader;

class Autoload
{
    /** @var string */
    private $namespace;

    /** @var ComposerClassLoader */
    private $classLoader;

    /** @var FileCompiler */
    private $compiler;

    /** @var TagsUpdater */
    private $tagsUpdater;

    /** @var Cache */
    private $cache;

    /** @var Standard */
    private $serializer;

    private function __construct(string $namespace, ComposerClassLoader $classLoader, Cache $cache = null)
    {
        $this->namespace = $namespace;
        $this->classLoader = $classLoader;
        $this->cache = $cache;
        $this->compiler = new FileCompiler($classLoader);
        $this->tagsUpdater = new TagsUpdater();
        $this->serializer = new Standard();
    }

    public static function register(string $namespace, Cache $cache = null): bool
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

        $loader = new self($namespace, $classLoader, is_null($cache) ? new NoCache() : $cache);

        return spl_autoload_register([$loader, 'load'], true, true);
    }

    public function load(string $class): void
    {
        if (substr($class, 0, strlen($this->namespace)) === $this->namespace) {
            $filename = $this->classLoader->findFile($class);
            if (file_exists($filename)) {
                if ($this->cache->exists($class)) {
                    $this->cache->load($class);
                } else {
                    $nodes = $this->compiler->compile($filename);

                    if (sizeof($nodes)) {
                        $this->tagsUpdater->applyNodes($filename, ...$nodes);
                        $this->cache->write($class, $this->serializer->prettyPrint($nodes));
                        $this->cache->load($class);
                    }
                }
            }
        }
    }
}
