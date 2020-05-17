<?php

namespace Mtarld\SymbokBundle\Autoload;

use function Composer\Autoload\includeFile;
use Mtarld\SymbokBundle\Cache\RuntimeClassCache;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * @internal
 * @final
 */
class Autoloader
{
    /** @var ReplacerInterface */
    private $replacer;

    /** @var LoggerInterface */
    private $logger;

    /** @var AutoloadFinder */
    private $autoloadFinder;

    /** @var array<string> */
    private $namespaces;

    /** @var RuntimeClassCache */
    private $classCache;

    public function __construct(
        ReplacerInterface $replacer,
        LoggerInterface $logger,
        AutoloadFinder $autoloadFinder,
        RuntimeClassCache $classCache,
        array $namespaces
    ) {
        $this->replacer = $replacer;
        $this->logger = $logger;
        $this->autoloadFinder = $autoloadFinder;
        $this->classCache = $classCache;
        $this->namespaces = $namespaces;
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    public function loadClass(string $classFqcn): void
    {
        if (!$this->isSymbokScope($classFqcn)) {
            return;
        }

        try {
            $classPath = $this->autoloadFinder->findClassPath($classFqcn);
        } catch (RuntimeException $e) {
            return;
        }

        $this->logger->notice('{class} replacing attempt', ['class' => $classFqcn]);

        $cachedClass = $this->classCache->cache($classFqcn, $classPath, $this->replacer);
        includeFile($cachedClass->getPath());

        $this->logger->notice('{class} replaced', ['class' => $classFqcn]);
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
