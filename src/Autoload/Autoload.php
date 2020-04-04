<?php

namespace Mtarld\SymbokBundle\Autoload;

use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Resource\FileResource;

class Autoload
{
    /** @var ReplacerInterface */
    private $replacer;

    /** @var LoggerInterface */
    private $logger;

    /** @var AutoloadFinder */
    private $autoloadFinder;

    /** @var array<string> */
    private $namespaces;

    /** @var string */
    private $cacheDir;

    /** @var bool */
    private $isDebug;

    public function __construct(
        ReplacerInterface $replacer,
        LoggerInterface $logger,
        AutoloadFinder $autoloadFinder,
        array $namespaces,
        string $cacheDir,
        bool $isDebug
    ) {
        $this->replacer = $replacer;
        $this->logger = $logger;
        $this->autoloadFinder = $autoloadFinder;
        $this->namespaces = $namespaces;
        $this->cacheDir = $cacheDir;
        $this->isDebug = $isDebug;
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    /**
     * @psalm-suppress UnresolvableInclude
     */
    public function loadClass(string $class): void
    {
        if (!$this->isSymbokScope($class)) {
            return;
        }

        try {
            $filename = $this->autoloadFinder->findFile($class);
        } catch (\RuntimeException $e) {
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
            if (0 === strpos($class, $namespace)) {
                return true;
            }
        }

        return false;
    }
}
