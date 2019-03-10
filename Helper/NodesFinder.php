<?php

namespace Mtarld\SymbokBundle\Helper;

use Mtarld\SymbokBundle\Exception\SymbokException;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as NodeClass;
use PhpParser\Node\Stmt\ClassMethod as NodeClassMethod;
use PhpParser\Node\Stmt\GroupUse as NodeGroupUse;
use PhpParser\Node\Stmt\Namespace_ as NodeNamespace;
use PhpParser\Node\Stmt\Property as NodeProperty;
use PhpParser\Node\Stmt\Use_ as NodeUse;

abstract class NodesFinder
{
    public static function findNamespace(Node ...$nodes): NodeNamespace
    {
        $namespaces = [];
        foreach ($nodes as $node) {
            if ($node instanceof NodeNamespace) {
                $namespaces[] = $node;
                if (sizeof($namespaces) > 1) {
                    throw new SymbokException('More that one namespace in file');
                }
            }
        }
        if (sizeof($namespaces) == 0) {
            throw new SymbokException('No namespace was found in file');
        }

        return $namespaces[0];
    }

    public static function findClass(Node ...$nodes): NodeClass
    {
        $classes = [];
        foreach ($nodes as $node) {
            if ($node instanceof NodeClass) {
                $classes[] = $node;
                if (sizeof($classes) > 1) {
                    throw new SymbokException('More that one class in file');
                }
            }
        }
        if (sizeof($classes) == 0) {
            throw new SymbokException('No class was found in file');
        }

        return $classes[0];
    }


    public static function findUses(Node ...$nodes): array
    {
        $uses = [];
        foreach ($nodes as $node) {
            if ($node instanceof NodeGroupUse) {
                foreach ($node->uses as $use) {
                    $uses[$use->alias] = $node->prefix->toString() . '\\' . $use->name->toString();
                }
            }
            if ($node instanceof NodeUse) {
                foreach ($node->uses as $use) {
                    if (!empty($use->alias)) {
                        $uses[$use->alias->name] = $use->name->toString();
                    } else {
                        $uses[] = $use->name->toString();
                    }
                }
            }
        }

        return $uses;
    }

    public static function findProperties(Node ...$nodes): array
    {
        $properties = [];
        foreach ($nodes as $node) {
            if ($node instanceof NodeProperty) {
                $properties[] = $node;
            }
        }

        return $properties;
    }

    public static function findMethods(Node ...$nodes): array
    {
        $methods = [];
        foreach ($nodes as $node) {
            if ($node instanceof NodeClassMethod) {
                $methods[] = $node;
            }
        }

        return $methods;
    }
}
