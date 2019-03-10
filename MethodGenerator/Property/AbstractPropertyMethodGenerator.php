<?php

namespace Mtarld\SymbokBundle\MethodGenerator\Property;

use Mtarld\SymbokBundle\Context\ContextHolder;
use Mtarld\SymbokBundle\Model\Statements;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
use phpDocumentor\Reflection\DocBlock\Serializer;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\ClassMethod;

abstract class AbstractPropertyMethodGenerator
{
    /** @var ContextHolder */
    protected $contextHolder;

    /** @var Serializer */
    private $docBlockSerializer;

    public function __construct(ContextHolder $contextHolder)
    {
        $this->contextHolder = $contextHolder;
        $this->docBlockSerializer = new Serializer();
    }

    final public function generate(SymbokProperty $property, SymbokClass $class): Statements
    {
        $statements = new Statements();
        $statements->add(
            new ClassMethod(
                $this->getName($property),
                [
                    'flags'      => $this->getFlags($property),
                    'params'     => $this->getParams($property, $class),
                    'stmts'      => $this->getStmts($property, $class),
                    'returnType' => $this->getReturnType($property, $class),
                ],
                [
                    'comments' => $this->getComments($property, $class)
                ]
            )
        );

        return $statements;
    }

    abstract protected function getName(SymbokProperty $property): string;

    abstract protected function getFlags(SymbokProperty $property): int;

    abstract protected function getParams(SymbokProperty $property, SymbokClass $class): array;

    abstract protected function getStmts(SymbokProperty $property, SymbokClass $class): array;

    abstract protected function getReturnType(SymbokProperty $property, SymbokClass $class): ?string;

    private function getComments(SymbokProperty $property, SymbokClass $class): array
    {
        $comments = [];
        foreach ($this->getCommentDocBlocks($property, $class) as $docBlock) {
            $comments[] = new Doc(
                $this->docBlockSerializer->getDocComment($docBlock)
            );
        }

        return $comments;
    }

    abstract protected function getCommentDocBlocks(SymbokProperty $property, SymbokClass $class): array;

    final protected function isNullable(SymbokProperty $property, SymbokClass $class): bool
    {
        $propertyMethodSpecificNullable = $this->methodRequiresNullable($property);
        if ($propertyMethodSpecificNullable !== null) {
            return $propertyMethodSpecificNullable;
        }

        $propertySpecificNullable = $property->getRules()->requiresNullable();
        if ($propertySpecificNullable !== null) {
            return $propertySpecificNullable;
        }

        $classSpecificNullable = $class->getRules()->requiresAllPropertiesNullable();
        if ($classSpecificNullable !== null) {
            return $classSpecificNullable;
        }

        return false;
    }

    abstract protected function methodRequiresNullable(SymbokProperty $property): ?bool;
}
