<?php

namespace Mtarld\SymbokBundle\MethodGenerator\Property;

use Mtarld\SymbokBundle\Helper\PropertyMethodNameBuilder;
use Mtarld\SymbokBundle\Helper\TypeResolver;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

class Getter extends AbstractPropertyMethodGenerator
{
    protected function getFlags(SymbokProperty $property): int
    {
        return Class_::MODIFIER_PUBLIC;
    }

    protected function getParams(SymbokProperty $property, SymbokClass $class): array
    {
        return [];
    }

    protected function getName(SymbokProperty $property): string
    {
        return PropertyMethodNameBuilder::buildGetterMethodName(
            $property->getName(),
            $property->getType()
        );
    }

    protected function getReturnType(SymbokProperty $property, SymbokClass $class): ?string
    {
        return TypeResolver::resolveType(
            $property->getType(),
            $this->isNullable($property, $class),
            $this->contextHolder->getContext()
        ) ?: null;
    }

    protected function getStmts(SymbokProperty $property, SymbokClass $class): array
    {
        $stmts = [];
        $stmts[] = new Node\Stmt\Return_(
            new Node\Expr\PropertyFetch(
                new Node\Expr\Variable('this'),
                $property->getName()
            )
        );

        return $stmts;
    }

    protected function getCommentDocBlocks(SymbokProperty $property, SymbokClass $class): array
    {
        $returnType = $this->isNullable($property, $class) ? new Compound([
            $property->getType(),
            new Null_()
        ]) : $property->getType();

        $docBlock = new DocBlock(
            'Retrieves ' . $property->getName(),
            null,
            [new Return_($returnType)],
            $this->contextHolder->getContext()
        );

        return [$docBlock];
    }

    protected function methodRequiresNullable(SymbokProperty $property): ?bool
    {
        return $property->getRules()->requiresGetterNullable();
    }
}
