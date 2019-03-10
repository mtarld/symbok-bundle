<?php

namespace Mtarld\SymbokBundle\Compiler\Generator\Method\Property\Impl;

use Mtarld\SymbokBundle\Compiler\Generator\Method\Property\AbstractPropertyMethodGenerator;
use Mtarld\SymbokBundle\Compiler\Helper\PropertyMethodNameBuilder;
use Mtarld\SymbokBundle\Compiler\Helper\TypeResolver;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

class Getter extends AbstractPropertyMethodGenerator
{
    protected function getName(): string
    {
        return PropertyMethodNameBuilder::buildGetterMethodName(
            $this->property->getName(),
            $this->property->getType()
        );
    }

    protected function getFlags(): int
    {
        return Class_::MODIFIER_PUBLIC;
    }

    protected function getParams(): array
    {
        return [];
    }

    protected function getReturnType(): ?string
    {
        return TypeResolver::resolveType($this->property->getType(), $this->isNullable(), $this->context) ?: null;
    }

    protected function getStmts(): array
    {
        $stmts = [];
        $stmts[] = new Node\Stmt\Return_(
            new Node\Expr\PropertyFetch(
                new Node\Expr\Variable('this'),
                $this->property->getName())
        );

        return $stmts;
    }

    protected function getCommentDocBlocks(): array
    {
        $returnType = $this->isNullable() ? new Compound([
            $this->property->getType(),
            new Null_()
        ]) : $this->property->getType();

        $docBlock = new DocBlock(
            'Retrieves ' . $this->property->getName(),
            null,
            [new Return_($returnType)],
            $this->context
        );

        return [$docBlock];
    }

    protected function isMethodRequiresNullable(): ?bool
    {
        return $this->propertyRules->requiresGetterNullable();
    }
}
