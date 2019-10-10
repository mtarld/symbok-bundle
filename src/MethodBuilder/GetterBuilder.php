<?php

namespace Mtarld\SymbokBundle\MethodBuilder;

use Mtarld\SymbokBundle\Behavior\GetterBehavior;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\MethodNameGenerator;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Builder\Method;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;

class GetterBuilder
{
    private $behavior;
    private $typeFormatter;

    public function __construct(
        GetterBehavior $behavior,
        TypeFormatter $typeFormatter
    ) {
        $this->behavior = $behavior;
        $this->typeFormatter = $typeFormatter;
    }

    public function build(SymbokProperty $property): ClassMethod
    {
        $propertyName = $property->getName();
        $methodName = $this->getMethodName($property);

        $methodBuilder = (new Method($methodName))->makePublic();

        $returnType = $this->typeFormatter->asString($property->getType(), $this->behavior->isNullable($property));
        if (is_string($returnType)) {
            $methodBuilder->setReturnType($returnType);
        }

        // return $this->prop;
        $methodBuilder->addStmt(
            new Return_(
                new PropertyFetch(new Variable('this'), $propertyName)
            )
        );

        return $methodBuilder->getNode();
    }

    private function getMethodName(SymbokProperty $property): string
    {
        if (!$property->getType() instanceof Boolean) {
            return MethodNameGenerator::generate($property->getName(), MethodNameGenerator::METHOD_GET);
        }

        $methodType = $this->behavior->hasHasPrefix($property) ? MethodNameGenerator::METHOD_HAS : MethodNameGenerator::METHOD_IS;

        return MethodNameGenerator::generate($property->getName(), $methodType);
    }
}
