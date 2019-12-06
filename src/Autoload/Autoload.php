<?php

namespace Mtarld\SymbokBundle\Autoload;

use Mtarld\SymbokBundle\Exception\RuntimeException;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Debug\DebugClassLoader;

class Autoload
{
    public static $classLoader;

    private $replacer;
    private $logger;
    private $namespaces;
    private $cacheDir;
    private $isDebug;

    public function __construct(
        ReplacerInterface $replacer,
        LoggerInterface $symbokLogger,
        array $config,
        string $cacheDir,
        bool $isDebug
    ) {
        $this->replacer = $replacer;
        $this->logger = $symbokLogger;
        $this->namespaces = $config['namespaces'];
        $this->cacheDir = $cacheDir;
        $this->isDebug = $isDebug;
    }

    public static function getClassLoader()
    {
        if (static::$classLoader instanceof \Composer\Autoload\ClassLoader) {
            return static::$classLoader;
        }

        $loaders = array_filter(spl_autoload_functions(), function ($loader) {
            return is_array($loader) && method_exists($loader[0], 'findFile');
        });

        if ($loader = array_shift($loaders)) {
            static::$classLoader = $loader[0];

            return static::$classLoader;
        }

        throw new RuntimeException('Unable to find '.DebugClassLoader::class);
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    public function loadClass(string $class): void
    {
        if (!$this->isSymbokScope($class)) {
            return;
        }

        $filename = self::getClassLoader()->findFile($class);
        if (!file_exists($filename)) {
            return;
        }

        $this->logger->notice('{class} replacing attempt', ['class' => $class]);

        $cacheFactory = new ConfigCacheFactory($this->isDebug);
        $cachedClass = $cacheFactory->cache(
            $this->cacheDir.$class.'.php',
            function (ConfigCacheInterface $cache) use ($class, $filename) {
                $cache->write($this->replacer->replace($class), [new FileResource($filename)]);
            }
        );

        require_once $cachedClass->getPath();

        $this->logger->notice('{class} replaced', ['class' => $class]);
    }

    private function isSymbokScope(string $class): bool
    {
        foreach ($this->namespaces as $namespace) {
            if (substr($class, 0, strlen($namespace)) === $namespace) {
                return true;
            }
        }

        return false;
    }
}
