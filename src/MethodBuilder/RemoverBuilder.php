<?php

namespace Mtarld\SymbokBundle\MethodBuilder;

use Mtarld\SymbokBundle\Behavior\SetterBehavior;
use Mtarld\SymbokBundle\MethodBuilder\RemoverBuilder\DoctrineStatements;
use Mtarld\SymbokBundle\MethodBuilder\RemoverBuilder\RegularStatements;
use Mtarld\SymbokBundle\Model\Relation\DoctrineRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\Inflector;
use Mtarld\SymbokBundle\Util\MethodManipulator;
use Mtarld\SymbokBundle\Util\MethodNameGenerator;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use PhpParser\Builder\Method;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * @internal
 * @final
 */
class RemoverBuilder
{
    /** @var SetterBehavior */
    private $behavior;

    /** @var MethodManipulator */
    private $manipulator;

    /** @var TypeFormatter */
    private $typeFormatter;

    /** @var RegularStatements */
    private $regularStatements;

    /** @var DoctrineStatements */
    private $doctrineStatements;

    public function __construct(
        SetterBehavior $behavior,
        MethodManipulator $manipulator,
        TypeFormatter $typeFormatter,
        RegularStatements $regularStatements,
        DoctrineStatements $doctrineStatements
    ) {
        $this->behavior = $behavior;
        $this->manipulator = $manipulator;
        $this->typeFormatter = $typeFormatter;
        $this->regularStatements = $regularStatements;
        $this->doctrineStatements = $doctrineStatements;
    }

    public function build(SymbokProperty $property): ClassMethod
    {
        $methodName = MethodNameGenerator::generate(Inflector::singularize($property->getName()), MethodNameGenerator::METHOD_REMOVE);
        $methodBuilder = (new Method($methodName))->makePublic();

        $this->addParams($methodBuilder, $property);
        $this->addStatements($methodBuilder, $property);
        $this->addReturn($methodBuilder, $property);

        return $methodBuilder->getNode();
    }

    private function addParams(Method $methodBuilder, SymbokProperty $property): void
    {
        $param = new Param(
            new Variable(Inflector::singularize($property->getName())),
            null,
            $this->getParamType($property)
        );

        $methodBuilder->addParam($param);
    }

    private function addStatements(Method $methodBuilder, SymbokProperty $property): void
    {
        $statements = $property->getRelation() instanceof DoctrineRelation
                    ? $this->doctrineStatements->getStatements($property)
                    : $this->regularStatements->getStatements($property)
        ;

        $methodBuilder->addStmts($statements);
    }

    private function addReturn(Method $methodBuilder, SymbokProperty $property): void
    {
        $this->behavior->isFluent($property)
            ? $this->manipulator->makeFluent($methodBuilder)
            : $this->manipulator->makeVoidReturn($methodBuilder)
        ;
    }

    private function getParamType(SymbokProperty $property): ?string
    {
        if (($relation = $property->getRelation()) instanceof DoctrineRelation) {
            return $relation->getTargetClassName();
        }

        return $this->typeFormatter->nestedAsPhpString($property->getType());
    }
}
