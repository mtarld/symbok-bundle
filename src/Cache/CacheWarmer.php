<?php

namespace Mtarld\SymbokBundle\Cache;

use Mtarld\SymbokBundle\Autoload\AutoloadFinder;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * @internal
 * @final
 */
class CacheWarmer implements CacheWarmerInterface
{
    /** @var AutoloadFinder */
    private $autoloadFinder;

    /** @var ReplacerInterface */
    private $replacer;

    /** @var PhpCodeParser */
    private $codeParser;

    /** @var PhpCodeFinder */
    private $codeFinder;

    /** @var RuntimeClassCache */
    private $cache;

    /** @var array */
    private $namespaces;

    public function __construct(
        AutoloadFinder $autoloadFinder,
        ReplacerInterface $replacer,
        PhpCodeParser $codeParser,
        PhpCodeFinder $codeFinder,
        RuntimeClassCache $cache,
        array $namespaces
    ) {
        $this->autoloadFinder = $autoloadFinder;
        $this->replacer = $replacer;
        $this->codeParser = $codeParser;
        $this->codeFinder = $codeFinder;
        $this->cache = $cache;
        $this->namespaces = $namespaces;
    }

    public function isOptional(): bool
    {
        return false;
    }

    /**
     * @param string $cacheDir
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->namespaces as $namespace) {
            $this->warmUpNamespace($namespace);
        }

        return [];
    }

    private function warmUpNamespace(string $namespace): void
    {
        foreach ($this->autoloadFinder->findClassPathsInNamespace($namespace) as $file) {
            $fqcn = $this->codeFinder->findFqcn($this->codeParser->parseStatementsFromPath($file->getPathname()));
            $this->cache->store($fqcn, $file->getPathname(), $this->replacer);
        }
    }
}
