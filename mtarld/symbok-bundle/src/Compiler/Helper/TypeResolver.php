<?php

namespace Mtarld\SymbokBundle\Compiler\Helper;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Null_;

abstract class TypeResolver
{
    public static function resolveType(Type $type, bool $nullable, Context $typeContext): string
    {
        if ($type instanceof Compound) {
            if ($type->getIterator()->count() > 2) {
                throw new \Exception("Too many types!");
            }
            $type = self::getMainType($type);
        }
        if ($type instanceof Array_) {
            return 'array';
        }
        foreach ($typeContext->getNamespaceAliases() as $alias => $namespace) {
            if ((string)$type === "\\{$namespace}") {
                $type = $alias;
            }
        }

        return ($nullable && $type !== null ? '?' : '') . (string)$type;
    }

    private static function getMainType(Compound $type): Type
    {
        foreach ($type as $typeObj) {
            if (!($typeObj instanceof Null_)) {
                return $typeObj;
            }
        }

        return null;
    }
}
