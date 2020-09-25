<?php

namespace Mtarld\SymbokBundle\Finder;

use Mtarld\SymbokBundle\Exception\CodeFindingException;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeFinder;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @final
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class PhpCodeFinder
{
    /** @var LoggerInterface */
    private $logger;

    /** @var NodeFinder */
    private $finder;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->finder = new NodeFinder();
    }

    /**
     * @param array<Node> $nodes
     */
    public function findNamespace(array $nodes): Namespace_
    {
        /** @var Namespace_[] $namespaces */
        $namespaces = $this->finder->findInstanceOf($nodes, Namespace_::class);
        if (count($namespaces) > 1) {
            throw new CodeFindingException('More than one namespace found');
        }
        if (empty($namespaces)) {
            throw new CodeFindingException('No namespace found');
        }

        return $namespaces[0];
    }

    /**
     * @param array<Node> $nodes
     *
     * @return array<string>
     */
    public function findAliases(array $nodes): array
    {
        $aliases = [];

        $uses = $this->finder->findInstanceOf($nodes, Use_::class);
        array_walk($uses, static function (Use_ $use) use (&$aliases): void {
            foreach ($use->uses as $realUse) {
                $alias = $realUse->alias ? (string) $realUse->alias : end($realUse->name->parts);
                $aliases[$alias] = (string) $realUse->name;
            }
        });

        $groupUses = $this->finder->findInstanceOf($nodes, GroupUse::class);
        array_walk($groupUses, static function (GroupUse $use) use (&$aliases): void {
            foreach ($use->uses as $realUse) {
                $alias = $realUse->alias ? (string) $realUse->alias : end($realUse->name->parts);
                $aliases[$alias] = $use->prefix.'\\'.$realUse->name;
            }
        });

        return $aliases;
    }

    /**
     * @param array<Node> $nodes
     */
    public function findClass(array $nodes): Class_
    {
        /** @var Class_[] $classes */
        $classes = $this->finder->findInstanceOf($nodes, Class_::class);
        if (count($classes) > 1) {
            throw new CodeFindingException('More than one class found');
        }
        if (empty($classes)) {
            throw new CodeFindingException('No class found');
        }

        return $classes[0];
    }

    /**
     * @param array<Node> $nodes
     *
     * @return array<Property>
     */
    public function findProperties(array $nodes): array
    {
        /** @var array<Property> $properties */
        $properties = $this->finder->findInstanceOf($nodes, Property::class);

        return $properties;
    }

    /**
     * @param array<Node> $nodes
     *
     * @return array<ClassMethod>
     */
    public function findMethods(array $nodes): array
    {
        /** @var array<ClassMethod> $classes */
        $classes = $this->finder->findInstanceOf($nodes, ClassMethod::class);

        return $classes;
    }

    /**
     * @param array<Node> $nodes
     */
    public function findMethod(string $methodName, array $nodes): ?ClassMethod
    {
        foreach ($this->findMethods($nodes) as $method) {
            if ($method->name->name === $methodName) {
                return $method;
            }
        }

        return null;
    }

    /**
     * @param array<Node> $nodes
     */
    public function hasMethod(string $methodName, array $nodes): bool
    {
        return $this->findMethod($methodName, $nodes) instanceof ClassMethod;
    }

    /**
     * @param array<Node> $nodes
     */
    public function findClassName(array $nodes): string
    {
        if (null === $name = $this->findClass($nodes)->name) {
            throw new CodeFindingException('Cannot find class name.');
        }

        return $name;
    }

    /**
     * @param array<Node> $nodes
     */
    public function findNamespaceName(array $nodes): string
    {
        if (null === $name = $this->findNamespace($nodes)->name) {
            throw new CodeFindingException('Cannot find namespace name.');
        }

        return $name;
    }

    public function isClass(array $nodes): bool
    {
        return count($this->finder->findInstanceOf($nodes, Class_::class)) > 0;
    }

    public function findFqcn(array $nodes): string
    {
        return $this->findNamespaceName($nodes).'\\'.$this->findClassName($nodes);
    }

    /**
     * @return Identifier|Name|null
     */
    public function findPropertyType(Property $property): ?Node
    {
        $type = $property->type;
        $type = $type instanceof NullableType ? $type->type : $type;

        if ($type instanceof Identifier) {
            $this->logger->debug('Found {type} type from typed property', ['type' => (string) $type]);

            return $type;
        }

        if ($type instanceof Name) {
            $this->logger->debug('Found {type} type from typed property', ['type' => (string) $type]);

            return $type;
        }

        return null;
    }
}
