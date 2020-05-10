<?php

namespace Mtarld\SymbokBundle\Autoload;

use Composer\Autoload\ClassLoader;
use RuntimeException;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\ErrorHandler\DebugClassLoader as ErrorHandlerDebugClassLoader;

/**
 * @internal
 * @final
 */
class AutoloadFinder
{
    /** @var string */
    private $namespace;

    /** @var ClassLoader|null */
    private $classLoader;

    public function __construct(string $namespace)
    {
        $this->namespace = rtrim($namespace, '\\').'\\';
    }

    public function findFile(string $class): string
    {
        $path = $this->getClassLoader()->findFile($class);
        if (!is_string($path)) {
            throw new RuntimeException(sprintf("Cannot find file related to class '%s'", $class));
        }

        return $path;
    }

    private function getClassLoader(): ClassLoader
    {
        if (null === $this->classLoader) {
            $this->classLoader = $this->findComposerClassLoader();
        }

        if (null === $this->classLoader) {
            throw new RuntimeException("Could not find a Composer autoloader that autoloads from '{$this->namespace}'");
        }

        return $this->classLoader;
    }

    private function findComposerClassLoader(): ?ClassLoader
    {
        if (false === $functions = spl_autoload_functions()) {
            return null;
        }

        foreach ($functions as $autoloader) {
            if (!is_array($autoloader)) {
                continue;
            }

            if (null === $classLoader = $this->extractComposerClassLoader($autoloader[0] ?? null)) {
                continue;
            }

            if ($this->isMatchingClassLoader($classLoader)) {
                return $classLoader;
            }
        }

        return null;
    }

    /**
     * @param mixed|null $autoloader
     *
     * @psalm-suppress UndefinedClass
     * @psalm-suppress InvalidArrayAccess
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function extractComposerClassLoader($autoloader): ?ClassLoader
    {
        if (isset($autoloader) && \is_object($autoloader)) {
            if ($autoloader instanceof ClassLoader) {
                return $autoloader;
            }

            if (($autoloader instanceof DebugClassLoader || $autoloader instanceof ErrorHandlerDebugClassLoader)
                && is_array($autoloader->getClassLoader())
                && $autoloader->getClassLoader()[0] instanceof ClassLoader) {
                return $autoloader->getClassLoader()[0];
            }
        }

        return null;
    }

    private function isMatchingClassLoader(ClassLoader $classLoader): ?ClassLoader
    {
        foreach (array_keys($classLoader->getPrefixesPsr4()) as $prefix) {
            if (0 === strpos($this->namespace, $prefix)) {
                return $classLoader;
            }
        }

        return null;
    }
}
