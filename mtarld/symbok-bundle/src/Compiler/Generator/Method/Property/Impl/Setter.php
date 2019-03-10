<?php

namespace Mtarld\SymbokBundle\Compiler\Generator\Method\Property\Impl;

use Doctrine\ORM\Mapping\ManyToOne;
use Mtarld\SymbokBundle\Compiler\Generator\Method\Property\AbstractPropertyMethodGenerator;
use Mtarld\SymbokBundle\Compiler\Helper\PropertyMethodNameBuilder;
use Mtarld\SymbokBundle\Compiler\Helper\TypeResolver;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Void_;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;

class Setter extends AbstractPropertyMethodGenerator
{
    protected function getName(): string
    {
        return PropertyMethodNameBuilder::buildSetterMethodName($this->property->getName());
    }

    protected function getFlags(): int
    {
        return Class_::MODIFIER_PUBLIC;
    }

    protected function getParams(): array
    {
        return [
            new Param(
                new Variable($this->property->getName()),
                null,
                TypeResolver::resolveType($this->property->getType(), $this->isNullable(), $this->context)
            )
        ];
    }

    protected function getStmts(): array
    {
        $stmts = [];

        $propertyName = $this->property->getName();
        $baseSetter = new Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    $propertyName
                ),
                new Node\Expr\Variable($propertyName)
            )
        );

        $doctrineAnnotation = $this->property->getDoctrineRelationAnnotation();
        if ($doctrineAnnotation) {
            $isManyRelation = $doctrineAnnotation->getRealAnnotation() instanceof ManyToOne;
            $className = $this->property->getClassName();

            if ($isManyRelation) {
                $removeOldRelation = new Expression(
                    new Node\Expr\MethodCall(
                        new Node\Expr\PropertyFetch(
                            new Node\Expr\Variable('this'),
                            $propertyName
                        ),
                        PropertyMethodNameBuilder::buildRemoverMethodName($className),
                        [new Node\Expr\Variable('this')]
                    )
                );
                $stmts[] = $removeOldRelation;

                $otherSideUpdate = new Node\Stmt\If_(
                    new Node\Expr\Variable($propertyName),
                    [
                        'stmts' => [
                            new Expression(
                                new Node\Expr\MethodCall(
                                    new Node\Expr\Variable($propertyName),
                                    PropertyMethodNameBuilder::buildAdderMethodName($className),
                                    [new Node\Expr\Variable('this')]
                                )
                            )
                        ]
                    ]
                );
            } else {
                $otherSideUpdate = new Node\Expr\MethodCall(
                    new Node\Expr\Variable($propertyName),
                    PropertyMethodNameBuilder::buildSetterMethodName($className),
                    [new Node\Expr\Variable('this')]
                );
            }
            $stmts[] = $otherSideUpdate;
        }
        $stmts[] = $baseSetter;

        if ($this->isFluent()) {
            $stmts[] = new Node\Stmt\Return_(
                new Node\Expr\Variable('this')
            );
        }

        return $stmts;
    }

    private function isFluent(): bool
    {
        $propertyMethodSpecificFluent = $this->propertyRules->requiresSetterFluent();
        if ($propertyMethodSpecificFluent !== null) {
            return $propertyMethodSpecificFluent;
        }

        return $this->classRules->requiresFluentSetters();
    }

    protected function getReturnType(): ?string
    {
        if ($this->isFluent()) {
            return (string)(new Self_());
        }

        return PHP_VERSION_ID < 700100 ? null : (string)(new Void_());
    }

    protected function getCommentDocBlocks(): array
    {
        $paramType = $this->isNullable() ? new Compound([
            $this->property->getType(),
            new Null_()
        ]) : $this->property->getType();

        $returnType = $this->isFluent() ? new Self_() : new Void_();

        $docBlock = new DocBlock(
            'Sets ' . $this->property->getName(),
            null,
            [
                new DocBlock\Tags\Param($this->property->getName(), $paramType),
                new DocBlock\Tags\Return_($returnType),
            ],
            $this->context
        );

        return [$docBlock];
    }

    protected function isMethodRequiresNullable(): ?bool
    {
        return $this->propertyRules->requiresSetterNullable();
    }
}
