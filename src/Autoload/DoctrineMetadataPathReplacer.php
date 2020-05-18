<?php

namespace Mtarld\SymbokBundle\Autoload;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

/**
 * @internal
 * @final
 */
class DoctrineMetadataPathReplacer
{
    /** @var AnnotationDriver|null */
    private $annotationDriver;

    /** @var AutoloadFinder */
    private $autoloadFinder;

    /** @var array<string> */
    private $namespaces;

    /** @var string */
    private $cacheDir;

    public function __construct(
        ?AnnotationDriver $annotationDriver,
        AutoloadFinder $autoloadFinder,
        array $namespaces,
        string $cacheDir
    ) {
        $this->annotationDriver = $annotationDriver;
        $this->autoloadFinder = $autoloadFinder;
        $this->namespaces = $namespaces;
        $this->cacheDir = $cacheDir;
    }

    public function replaceWithSymbokPath(): void
    {
        if (null === $this->annotationDriver) {
            return;
        }

        // Exclude old doctrine paths
        $this->annotationDriver->addExcludePaths(array_map(function (string $namespace) {
            return $this->autoloadFinder->findNamespacePath($namespace);
        }, $this->namespaces));

        // To replace them by the relative cached ones
        $this->annotationDriver->addPaths(array_map(function (string $namespace) {
            return sprintf('%s/%s', $this->cacheDir, str_replace('\\', '/', $namespace));
        }, $this->namespaces));
    }
}
