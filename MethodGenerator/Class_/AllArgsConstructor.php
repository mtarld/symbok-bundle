<?php

namespace Mtarld\SymbokBundle\MethodGenerator\Class_;

use Mtarld\SymbokBundle\Helper\TypeResolver;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;

class AllArgsConstructor extends AbstractClassMethodGenerator
{
    protected function getName(): string
    {
        return '__construct';
    }

    protected function getFlags(): int
    {
        return Class_::MODIFIER_PUBLIC;
    }

    protected function getParams(SymbokClass $class): array
    {
        $properties = $class->getProperties();

        return array_map(function (SymbokProperty $property) use ($class) {
            $propertyNullable = $this->isPropertyNullable($class, $property);
            if ($propertyNullable) {
                return new Node\Param(
                    new Node\Expr\Variable($property->getName()),
                    new Node\Expr\ConstFetch(new Node\Name('null')),
                    TypeResolver::resolveType(
                        $property->getType(),
                        $propertyNullable,
                        $this->contextHolder->getContext()
                    )
                );
            } else {
                return new Node\Param(
                    new Node\Expr\Variable($property->getName()),
                    null,
                    TypeResolver::resolveType(
                        $property->getType(),
                        $propertyNullable,
                        $this->contextHolder->getContext()
                    )
                );
            }
        }, $properties);
    }

    private function isPropertyNullable(SymbokClass $class, SymbokProperty $property): bool
    {
        $propertySpecificNullable = $property->getRules()->requiresNullable();
        if ($propertySpecificNullable !== null) {
            return $propertySpecificNullable;
        }

        return $class->getRules()->requiresConstructorNullable();
    }

    protected function getStmts(SymbokClass $class): array
    {
        $properties = $class->getProperties();

        return array_map(function (SymbokProperty $property) {
            return new Expression(
                new Node\Expr\Assign(
                    new Node\Expr\PropertyFetch(
                        new Node\Expr\Variable('this'),
                        $property->getName()
                    ),
                    new Node\Expr\Variable($property->getName())
                )
            );
        }, $properties);
    }

    protected function getReturnType(): ?string
    {
        return null;
    }

    protected function getCommentDocBlocks(SymbokClass $class): array
    {
        $properties = $class->getProperties();
        $docBlock = new DocBlock(
            $class->getName() . ' constructor.',
            null,
            array_map(function (SymbokProperty $property) use ($class) {
                $paramType = $this->isPropertyNullable($class, $property) ? new Compound([
                    $property->getType(),
                    new Null_()
                ]) : $property->getType();

                return new DocBlock\Tags\Param($property->getName(), $paramType);
            }, $properties),
            $this->contextHolder->getContext()
        );

        return [$docBlock];
    }
}
