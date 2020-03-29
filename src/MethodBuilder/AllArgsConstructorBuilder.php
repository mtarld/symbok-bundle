<?php

namespace Mtarld\SymbokBundle\MethodBuilder;

use Mtarld\SymbokBundle\Behavior\AllArgsConstructorBehavior;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Nullable;
use PhpParser\Builder\Method;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;

class AllArgsConstructorBuilder
{
    /** @var AllArgsConstructorBehavior */
    private $behavior;

    /** @var TypeFormatter */
    private $typeFormatter;

    public function __construct(
        AllArgsConstructorBehavior $behavior,
        TypeFormatter $typeFormatter
    ) {
        $this->behavior = $behavior;
        $this->typeFormatter = $typeFormatter;
    }

    public function build(SymbokClass $class): ClassMethod
    {
        $methodBuilder = (new Method('__construct'))->makePublic();

        $methodBuilder->addParams($this->getParams($class));
        $methodBuilder->addStmts($this->getAssignStatements($class));

        return $methodBuilder->getNode();
    }

    /**
     * @return array<Node>
     */
    private function getParams(SymbokClass $class): array
    {
        return array_map(function (SymbokProperty $property): Node {
            $default = $this->behavior->isNullable($property)
                ? new ConstFetch(new Name('null'))
                : null
            ;

            if (($type = $property->getType()) instanceof Type) {
                $type = $this->behavior->isNullable($property) ? new Nullable($type) : $type;
            }

            return new Param(
                new Variable($property->getName()),
                $default,
                $this->typeFormatter->asPhpString($type)
            );
        }, $class->getProperties());
    }

    /**
     * @return array<Node>
     */
    private function getAssignStatements(SymbokClass $class): array
    {
        return array_map(static function (SymbokProperty $property): Node {
            // $this->prop = $prop;
            return new Expression(
                new Assign(
                    new PropertyFetch(
                        new Variable('this'),
                        $property->getName()
                    ),
                    new Variable($property->getName())
                )
            );
        }, $class->getProperties());
    }
}
