<?php

namespace Mtarld\SymbokBundle\MethodBuilder;

use Mtarld\SymbokBundle\Behavior\SetterBehavior;
use Mtarld\SymbokBundle\Model\Relation\OneToOneRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\MethodManipulator;
use Mtarld\SymbokBundle\Util\MethodNameGenerator;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Nullable;
use PhpParser\Builder\Method;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;

class SetterBuilder
{
    /** @var SetterBehavior */
    private $behavior;

    /** @var MethodManipulator */
    private $manipulator;

    /** @var TypeFormatter */
    private $typeFormatter;

    public function __construct(
        SetterBehavior $behavior,
        MethodManipulator $manipulator,
        TypeFormatter $typeFormatter
    ) {
        $this->behavior = $behavior;
        $this->manipulator = $manipulator;
        $this->typeFormatter = $typeFormatter;
    }

    public function build(SymbokProperty $property): ClassMethod
    {
        $methodName = MethodNameGenerator::generate($property->getName(), MethodNameGenerator::METHOD_SET);
        $methodBuilder = (new Method($methodName))->makePublic();

        $this->addParams($methodBuilder, $property);
        $this->addStatements($methodBuilder, $property);
        $this->addReturn($methodBuilder, $property);

        return $methodBuilder->getNode();
    }

    private function addParams(Method $methodBuilder, SymbokProperty $property): void
    {
        if (($type = $property->getType()) instanceof Type) {
            $type = $this->behavior->isNullable($property) ? new Nullable($type) : $type;
        }
        $param = new Param(
            new Variable($property->getName()),
            null,
            $this->typeFormatter->asPhpString($type)
        );

        $methodBuilder->addParam($param);
    }

    private function addStatements(Method $methodBuilder, SymbokProperty $property): void
    {
        $statements = [$this->getOwnSideStatement($property->getName())];

        if ($this->behavior->hasToUpdateOtherSide($property)) {
            $relation = $property->getRelation();
            if ($relation instanceof OneToOneRelation && !$relation->isOwning()) {
                $statements = array_merge($statements, $this->getOtherSideStatements($relation, $property->getName()));
            }
        }

        $methodBuilder->addStmts($statements);
    }

    private function addReturn(Method $methodBuilder, SymbokProperty $property): void
    {
        $this->behavior->isFluent($property)
            ? $this->manipulator->makeFluent($methodBuilder)
            : $this->manipulator->makeVoidReturn($methodBuilder)
        ;
    }

    private function getOwnSideStatement(string $propertyName): Node
    {
        // $this->prop = $prop;
        return new Expression(
            new Assign(
                new PropertyFetch(new Variable('this'), $propertyName),
                new Variable($propertyName)
            )
        );
    }

    /**
     * @return array<Node>
     */
    private function getOtherSideStatements(OneToOneRelation $relation, string $propertyName): array
    {
        return [
            // $new = null === $name ? null : $this;
            new Assign(
                new Variable('new'),
                new Ternary(
                    new Identical(new ConstFetch(new Name('null')), new Variable($propertyName)),
                    new ConstFetch(new Name('null')),
                    new Variable('this')
                )
            ),
            // if ($name->getTarget() !== $new) {
            new If_(
                new NotIdentical(
                    new MethodCall(
                        new Variable($propertyName),
                        $relation->getTargetGetterMethodName()
                    ),
                    new Variable('new')
                ),
                [
                    'stmts' => [
                        // $name->setTarget($new);
                        new Expression(
                            new MethodCall(
                                new Variable($propertyName),
                                $relation->getTargetSetterMethodName(),
                                [new Arg(new Variable('new'))]
                            )
                        ),
                    ],
                ]
            ),
        ];
    }
}
