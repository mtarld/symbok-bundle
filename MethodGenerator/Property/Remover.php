<?php

namespace Mtarld\SymbokBundle\MethodGenerator\Property;

use Doctrine\ORM\Mapping\ManyToMany;
use Mtarld\SymbokBundle\Exception\SymbokException;
use Mtarld\SymbokBundle\Helper\PropertyMethodNameBuilder;
use Mtarld\SymbokBundle\Helper\Singularize;
use Mtarld\SymbokBundle\Helper\TypeResolver;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Void_;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;

class Remover extends AbstractPropertyMethodGenerator
{
    protected function getName(SymbokProperty $property): string
    {
        return PropertyMethodNameBuilder::buildRemoverMethodName($property->getName());
    }

    protected function getFlags(SymbokProperty $property): int
    {
        return Class_::MODIFIER_PUBLIC;
    }

    protected function getParams(SymbokProperty $property, SymbokClass $class): array
    {
        return [
            new Param(
                new Variable(Singularize::getSingular($property->getName())),
                null,
                TypeResolver::resolveType($property->getRelationType(), false, $this->contextHolder->getContext())
            )
        ];
    }

    protected function getReturnType(SymbokProperty $property, SymbokClass $class): ?string
    {
        if ($this->isFluent($property, $class)) {
            return (string)(new Self_());
        }

        return PHP_VERSION_ID < 700100 ? null : (string)(new Void_());
    }

    private function isFluent(SymbokProperty $property, SymbokClass $class): bool
    {
        $propertyMethodSpecificFluent = $property->getRules()->requiresSetterFluent();
        if ($propertyMethodSpecificFluent !== null) {
            return $propertyMethodSpecificFluent;
        }

        return $class->getRules()->requiresFluentSetters();
    }

    protected function getStmts(SymbokProperty $property, SymbokClass $class): array
    {
        $doctrineAnnotation = $property->getDoctrineRelationAnnotation();

        if (!$doctrineAnnotation) {
            throw new SymbokException('Cannot read doctrine annotation');
        }

        $propertyName = $property->getName();
        $variableName = Singularize::getSingular($propertyName);
        $className = $class->getName();
        $isManyRelation = $doctrineAnnotation->getRealAnnotation() instanceof ManyToMany;

        $ifCondition = new Node\Expr\MethodCall(
            new Node\Expr\PropertyFetch(
                new Node\Expr\Variable('this'),
                $propertyName
            ),
            'contains',
            [new Node\Expr\Variable($variableName)]
        );

        if ($isManyRelation) {
            $otherSideUpdate = new Expression(
                new Node\Expr\MethodCall(
                    new Node\Expr\Variable($variableName),
                    PropertyMethodNameBuilder::buildRemoverMethodName($className),
                    [new Node\Expr\Variable('this')]
                )
            );
        } else {
            $otherSideUpdate = new Expression(
                new Node\Expr\MethodCall(
                    new Node\Expr\Variable($variableName),
                    PropertyMethodNameBuilder::buildSetterMethodName($className),
                    [new Node\Expr\ConstFetch(new Node\Name('null'))]
                )
            );
        }

        $stmts = [];
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
                            'removeElement',
                            [new Node\Expr\Variable($variableName)]
                        )
                    ),
                    $otherSideUpdate
                ]
            ]
        );

        if ($this->isFluent($property, $class)) {
            $stmts[] = new Node\Stmt\Return_(
                new Node\Expr\Variable('this')
            );
        }

        return $stmts;
    }

    protected function getCommentDocBlocks(SymbokProperty $property, SymbokClass $class): array
    {
        $returnType = $this->isFluent($property, $class) ? new Self_() : new Void_();

        $docBlock = new DocBlock(
            'Removes ' . Singularize::getSingular($property->getName()) . ' to ' . $property->getName(),
            null,
            [
                new DocBlock\Tags\Param(
                    Singularize::getSingular($property->getName()),
                    $property->getRelationType()
                ),
                new DocBlock\Tags\Return_($returnType),
            ],
            $this->contextHolder->getContext()
        );

        return [$docBlock];
    }

    protected function isMethodRequiresNullable(): bool
    {
        return false;
    }

    protected function methodRequiresNullable(SymbokProperty $property): ?bool
    {
        return false;
    }
}
