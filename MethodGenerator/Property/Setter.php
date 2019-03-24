<?php

namespace Mtarld\SymbokBundle\MethodGenerator\Property;

use Doctrine\ORM\Mapping\ManyToOne;
use Mtarld\SymbokBundle\Helper\PropertyMethodNameBuilder;
use Mtarld\SymbokBundle\Helper\TypeResolver;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
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
    protected function getName(SymbokProperty $property): string
    {
        return PropertyMethodNameBuilder::buildSetterMethodName($property->getName());
    }

    protected function getFlags(SymbokProperty $property): int
    {
        return Class_::MODIFIER_PUBLIC;
    }

    protected function getParams(SymbokProperty $property, SymbokClass $class): array
    {
        return [
            new Param(
                new Variable($property->getName()),
                null,
                TypeResolver::resolveType(
                    $property->getType(),
                    $this->isNullable($property, $class),
                    $this->contextHolder->getContext()
                )
            )
        ];
    }

    protected function getStmts(SymbokProperty $property, SymbokClass $class): array
    {
        $stmts = [];

        $propertyName = $property->getName();
        $baseSetter = new Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    $propertyName
                ),
                new Node\Expr\Variable($propertyName)
            )
        );

        $doctrineAnnotation = $property->getDoctrineRelationAnnotation();
        if ($doctrineAnnotation) {
            $isManyRelation = $doctrineAnnotation->getRealAnnotation() instanceof ManyToOne;
            $className = $class->getName();

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
                $otherSideUpdate = new Expression(
                    new Node\Expr\MethodCall(
                        new Node\Expr\Variable($propertyName),
                        PropertyMethodNameBuilder::buildSetterMethodName($className),
                        [new Node\Expr\Variable('this')]
                    )
                );
            }
            $stmts[] = $otherSideUpdate;
        }
        $stmts[] = $baseSetter;

        if ($this->isFluent($property, $class)) {
            $stmts[] = new Node\Stmt\Return_(
                new Node\Expr\Variable('this')
            );
        }

        return $stmts;
    }

    private function isFluent(SymbokProperty $property, SymbokClass $class): bool
    {
        $propertyMethodSpecificFluent = $property->getRules()->requiresSetterFluent();
        if ($propertyMethodSpecificFluent !== null) {
            return $propertyMethodSpecificFluent;
        }

        return $class->getRules()->requiresFluentSetters();
    }

    protected function getReturnType(SymbokProperty $property, SymbokClass $class): ?string
    {
        if ($this->isFluent($property, $class)) {
            return (string)(new Self_());
        }

        return PHP_VERSION_ID < 700100 ? null : (string)(new Void_());
    }

    protected function getCommentDocBlocks(SymbokProperty $property, SymbokClass $class): array
    {
        $paramType = $this->isNullable($property, $class) ? new Compound([
            $property->getType(),
            new Null_()
        ]) : $property->getType();

        $returnType = $this->isFluent($property, $class) ? new Self_() : new Void_();

        $docBlock = new DocBlock(
            'Sets ' . $property->getName(),
            null,
            [
                new DocBlock\Tags\Param($property->getName(), $paramType),
                new DocBlock\Tags\Return_($returnType),
            ],
            $this->contextHolder->getContext()
        );

        return [$docBlock];
    }

    protected function methodRequiresNullable(SymbokProperty $property): ?bool
    {
        return $property->getRules()->requiresSetterNullable();
    }
}
