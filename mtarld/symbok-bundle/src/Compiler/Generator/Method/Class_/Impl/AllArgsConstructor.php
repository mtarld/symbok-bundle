<?php

namespace Mtarld\SymbokBundle\Compiler\Generator\Method\Class_\Impl;

use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedProperty;
use Mtarld\SymbokBundle\Compiler\Generator\Method\Class_\AbstractClassMethodGenerator;
use Mtarld\SymbokBundle\Compiler\Helper\TypeResolver;
use Mtarld\SymbokBundle\Compiler\Rules\Impl\PropertyRules;
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

    protected function getParams(): array
    {
        $properties = $this->class->getProperties();
        return array_map(function (ParsedProperty $property) {
            $propertyNullable = $this->isPropertyNullable($property);
            if ($propertyNullable) {
                return new Node\Param(
                    new Node\Expr\Variable($property->getName()),
                    new Node\Expr\ConstFetch(new Node\Name('null')),
                    TypeResolver::resolveType($property->getType(), $propertyNullable, $this->context)
                );
            } else {
                return new Node\Param(
                    new Node\Expr\Variable($property->getName()),
                    null,
                    TypeResolver::resolveType($property->getType(), $propertyNullable, $this->context)
                );
            }
        }, $properties);
    }

    private function isPropertyNullable(ParsedProperty $property): bool
    {
        $propertyRules = new PropertyRules($property);

        $propertySpecificNullable = $propertyRules->requiresNullable();
        if ($propertySpecificNullable !== null) {
            return $propertySpecificNullable;
        }

        return $this->classRules->requiresConstructorNullable();
    }

    protected function getStmts(): array
    {
        $properties = $this->class->getProperties();
        return array_map(function (ParsedProperty $property) {
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

    protected function getCommentDocBlocks(): array
    {
        $properties = $this->class->getProperties();
        $docBlock = new DocBlock(
            $this->class->getName() . ' constructor.',
            null,
            array_map(function (ParsedProperty $property) {
                $paramType = $this->isPropertyNullable($property) ? new Compound([
                    $property->getType(),
                    new Null_()
                ]) : $property->getType();
                return new DocBlock\Tags\Param($property->getName(), $paramType);
            }, $properties),
            $this->context
        );

        return [$docBlock];
    }
}
