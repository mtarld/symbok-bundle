<?php

namespace Mtarld\SymbokBundle\Compiler\Generator\Method\Property\Impl;

use Doctrine\ORM\Mapping\ManyToMany;
use Mtarld\SymbokBundle\Compiler\Generator\Method\Property\AbstractPropertyMethodGenerator;
use Mtarld\SymbokBundle\Compiler\Helper\PropertyMethodNameBuilder;
use Mtarld\SymbokBundle\Compiler\Helper\Singularize;
use Mtarld\SymbokBundle\Compiler\Helper\TypeResolver;
use Mtarld\SymbokBundle\Exception\SymbokException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Void_;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;

class Adder extends AbstractPropertyMethodGenerator
{
    protected function getName(): string
    {
        return PropertyMethodNameBuilder::buildAdderMethodName($this->property->getName());
    }

    protected function getFlags(): int
    {
        return Class_::MODIFIER_PUBLIC;
    }

    protected function getParams(): array
    {
        return [
            new Param(
                new Variable(Singularize::getSingular($this->property->getName())),
                null,
                TypeResolver::resolveType($this->property->getRelationType(), false, $this->context)
            )
        ];
    }

    protected function getReturnType(): ?string
    {
        if ($this->isFluent()) {
            return (string)(new Self_());
        }

        return PHP_VERSION_ID < 700100 ? null : (string)(new Void_());
    }

    private function isFluent(): bool
    {
        $propertyMethodSpecificFluent = $this->propertyRules->requiresSetterFluent();
        if ($propertyMethodSpecificFluent !== null) {
            return $propertyMethodSpecificFluent;
        }

        return $this->classRules->requiresFluentSetters();
    }

    protected function getStmts(): array
    {
        $stmts = [];
        $doctrineAnnotation = $this->property->getDoctrineRelationAnnotation();
        if (!$doctrineAnnotation) {

            throw new SymbokException('Cannot read doctrine annotation');
        }

        $propertyName = $this->property->getName();
        $variableName = Singularize::getSingular($propertyName);
        $className = $this->property->getClassName();
        $isManyRelation = $doctrineAnnotation->getRealAnnotation() instanceof ManyToMany;

        $ifCondition = new Node\Expr\BooleanNot(
            new Node\Expr\MethodCall(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    $propertyName
                ),
                'contains',
                [new Node\Expr\Variable($variableName)]
            )
        );

        if ($isManyRelation) {
            $otherSideUpdate = new Expression(
                new Node\Expr\MethodCall(
                    new Node\Expr\Variable($variableName),
                    PropertyMethodNameBuilder::buildAdderMethodName($className),
                    [new Node\Expr\Variable('this')]
                )
            );
        } else {
            $otherSideUpdate = new Expression(
                new Node\Expr\MethodCall(
                    new Node\Expr\Variable($variableName),
                    PropertyMethodNameBuilder::buildSetterMethodName($className),
                    [new Node\Expr\Variable('this')]
                )
            );
        }

        $stmts[] = new Node\Stmt\If_(
            $ifCondition,
            [
                'stmts' => [
                    new Expression(
                        new Node\Expr\MethodCall(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                $propertyName
                            ),
                            'add',
                            [new Node\Expr\Variable($variableName)]
                        )
                    ),
                    $otherSideUpdate
                ]
            ]
        );

        if ($this->isFluent()) {
            $stmts[] = new Node\Stmt\Return_(
                new Node\Expr\Variable('this')
            );
        }

        return $stmts;
    }

    protected function getCommentDocBlocks(): array
    {
        $returnType = $this->isFluent() ? new Self_() : new Void_();

        $docBlock = new DocBlock(
            'Adds ' . Singularize::getSingular($this->property->getName()) . ' to ' . $this->property->getName(),
            null,
            [
                new DocBlock\Tags\Param(
                    Singularize::getSingular($this->property->getName()),
                    $this->property->getRelationType()
                ),
                new DocBlock\Tags\Return_($returnType),
            ],
            $this->context
        );

        return [$docBlock];
    }

    protected function isMethodRequiresNullable(): bool
    {
        return false;
    }
}
