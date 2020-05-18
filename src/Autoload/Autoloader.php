<?php

namespace Mtarld\SymbokBundle\Autoload;

use function Composer\Autoload\includeFile;
use Mtarld\SymbokBundle\Cache\RuntimeClassCache;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @internal
 * @final
 */
class Autoloader implements ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    private $locator;

    /** @var LoggerInterface */
    private $logger;

    /** @var AutoloadFinder */
    private $autoloadFinder;

    /** @var array<string> */
    private $namespaces;

    /** @var RuntimeClassCache */
    private $classCache;

    public function __construct(
        ContainerInterface $locator,
        LoggerInterface $logger,
        AutoloadFinder $autoloadFinder,
        RuntimeClassCache $classCache,
        array $namespaces
    ) {
        $this->locator = $locator;
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

        $cachedClass = $this->classCache->cache($classFqcn, $classPath, $this->locator->get('symbok.replacer.runtime'));
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

    public static function getSubscribedServices(): array
    {
        return [
            'symbok.replacer.runtime' => ReplacerInterface::class,
        ];
    }
}
