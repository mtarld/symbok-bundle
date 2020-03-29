<?php

namespace Mtarld\SymbokBundle\Util;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\ClassString;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Resource_;
use phpDocumentor\Reflection\Types\Scalar;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\This;
use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;

class TypeFormatter
{
    /** @var TypeResolver */
    private $typeResolver;

    public function __construct()
    {
        $this->typeResolver = new TypeResolver();
    }

    /**
     * @param mixed|null $item
     */
    public function asDocumentationString($item): string
    {
        if (null === $item) {
            return '';
        }

        if ($item instanceof Type) {
            return $this->typeAsDocumentationString($item);
        }

        if ($item instanceof Node) {
            return $this->nodeAsDocumentationString($item);
        }

        return '';
    }

    /**
     * @param mixed|null $item
     */
    public function asPhpString($item): ?string
    {
        if (null === $item) {
            return null;
        }

        if ($item instanceof Type) {
            return $this->typeAsPhpString($item);
        }

        if ($item instanceof Node) {
            return $this->nodeAsPhpString($item);
        }

        return null;
    }

    public function nestedAsPhpString(?Type $type): ?string
    {
        if ($type instanceof Array_) {
            return $this->asPhpString($type->getValueType());
        }

        return $this->asPhpString($type);
    }

    /**
     * @param mixed|null $item
     */
    public function asDocumentationType($item): Type
    {
        $typeString = $this->asDocumentationString($item);

        return !empty($typeString) ? $this->stringAsType($typeString) : new Mixed_();
    }

    public function stringAsType(string $type): Type
    {
        return $this->typeResolver->resolve($type);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function typeAsPhpString(Type $type): ?string
    {
        switch (get_class($type)) {
            case Mixed_::class:
            case Compound::class:
            case ClassString::class:
            case Null_::class:
            case Resource_::class:
            case Static_::class:
            case Scalar::class:
            case This::class:
                return null;
            case Nullable::class:
                return (null === $actualType = $this->asPhpString($type->getActualType())) ? null : '?'.$actualType;
            case Array_::class:
                return 'array';
            case Iterable_::class:
                return 'iterable';
            case Collection::class:
                return $type->getFqsen();
            default:
                return (string) $type;
        }
    }

    private function nodeAsPhpString(Node $node): ?string
    {
        if ($node instanceof UnionType) {
            return null;
        }

        if ($node instanceof NullableType) {
            return ('' === $actualType = (string) $node->type) ? null : '?'.$actualType;
        }

        if (!method_exists($node, 'toString')) {
            return null;
        }

        return $node->toString();
    }

    private function typeAsDocumentationString(Type $type): string
    {
        if ($type instanceof Nullable) {
            return '?'.$this->asDocumentationString($type->getActualType());
        }

        if ($type instanceof Compound) {
            $types = [];
            foreach ($type as $subType) {
                $types[] = $this->asDocumentationString($subType);
            }

            return implode('|', $types);
        }

        return (string) $type;
    }

    private function nodeAsDocumentationString(Node $node): string
    {
        if ($node instanceof NullableType) {
            return '?'.$this->asDocumentationString($node->type);
        }

        if ($node instanceof UnionType) {
            foreach ($node->types as &$type) {
                $type = $this->asDocumentationString($type);
            }

            return implode('|', $node->types);
        }

        if (!method_exists($node, 'toString')) {
            return '';
        }

        return $node->toString();
    }
}
