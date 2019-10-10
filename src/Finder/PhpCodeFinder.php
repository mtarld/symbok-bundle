<?php

namespace Mtarld\SymbokBundle\Finder;

use Mtarld\SymbokBundle\Exception\CodeFindingException;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeFinder;

class PhpCodeFinder
{
    private $finder;

    public function __construct()
    {
        $this->finder = new NodeFinder();
    }

    public function findNamespace(array $statements): Namespace_
    {
        /** @var Namespace_[] $namespaces */
        $namespaces = $this->finder->findInstanceOf($statements, Namespace_::class);
        if (sizeof($namespaces) > 1) {
            throw new CodeFindingException('More than one namespace found');
        }
        if (empty($namespaces)) {
            throw new CodeFindingException('No namespace found');
        }

        return $namespaces[0];
    }

    public function findAliases(array $statements): array
    {
        $aliases = [];

        $uses = $this->finder->findInstanceOf($statements, Use_::class);
        array_walk($uses, function (Use_ $use) use (&$aliases) {
            foreach ($use->uses as $realUse) {
                $alias = $realUse->alias ? (string) $realUse->alias : end($realUse->name->parts);
                $aliases[$alias] = (string) $realUse->name;
            }
        });

        $groupUses = $this->finder->findInstanceOf($statements, GroupUse::class);
        array_walk($groupUses, function (GroupUse $use) use (&$aliases) {
            foreach ($use->uses as $realUse) {
                $alias = $realUse->alias ? (string) $realUse->alias : end($realUse->name->parts);
                $aliases[$alias] = $use->prefix.'\\'.$realUse->name;
            }
        });

        return $aliases;
    }

    public function findClass(array $statements): Class_
    {
        /** @var Class_[] $classes */
        $classes = $this->finder->findInstanceOf($statements, Class_::class);
        if (sizeof($classes) > 1) {
            throw new CodeFindingException('More than one class found');
        }
        if (empty($classes)) {
            throw new CodeFindingException('No class found');
        }

        return $classes[0];
    }

    public function findProperties(array $statements): array
    {
        return $this->finder->findInstanceOf($statements, Property::class);
    }

    public function findMethods(array $statements): array
    {
        return $this->finder->findInstanceOf($statements, ClassMethod::class);
    }

    public function findMethod(string $methodName, array $statements): ?ClassMethod
    {
        foreach ($this->findMethods($statements) as $method) {
            if ($method->name->name === $methodName) {
                return $method;
            }
        }

        return null;
    }

    public function hasMethod(string $methodName, array $statements): bool
    {
        return $this->findMethod($methodName, $statements) instanceof ClassMethod;
    }
}
