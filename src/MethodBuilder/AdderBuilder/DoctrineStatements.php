<?php

namespace Mtarld\SymbokBundle\MethodBuilder\AdderBuilder;

use Mtarld\SymbokBundle\Behavior\SetterBehavior;
use Mtarld\SymbokBundle\Model\Relation\DoctrineRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\Inflector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;

/**
 * @internal
 * @final
 */
class DoctrineStatements
{
    /** @var SetterBehavior */
    private $behavior;

    public function __construct(SetterBehavior $behavior)
    {
        $this->behavior = $behavior;
    }

    /**
     * @return array<Node>
     */
    public function getStatements(SymbokProperty $property): array
    {
        $propertyName = $property->getName();
        $paramName = Inflector::singularize($propertyName);

        $statements = [$this->getOwnSideUpdateStmt($propertyName, $paramName)];

        if ($this->behavior->hasToUpdateOtherSide($property) && ($relation = $property->getRelation()) instanceof DoctrineRelation && $relation->isOwning()) {
            $statements[] = $this->getOtherSideUpdateStmt($relation, $paramName);
        }

        return [
            // if (!$this->cars->contains($car)) {
            new If_(
                new BooleanNot(
                    new MethodCall(
                        new PropertyFetch(
                            new Variable('this'),
                            $propertyName
                        ),
                        'contains',
                        [
                            new Arg(new Variable($paramName)),
                        ]
                    )
                ),
                [
                    'stmts' => $statements,
                ]
            ),
        ];
    }

    private function getOwnSideUpdateStmt(string $propertyName, string $paramName): Stmt
    {
        // $this->cars->add($car);
        return new Expression(
            new MethodCall(
                new PropertyFetch(
                    new Variable('this'),
                    $propertyName
                ),
                'add',
                [
                    new Arg(new Variable($paramName)),
                ]
            )
        );
    }

    private function getOtherSideUpdateStmt(DoctrineRelation $relation, string $paramName): Stmt
    {
        //  $car->addUser($this); | $car->setUser($this);
        return new Expression(
            new MethodCall(
                new Variable($paramName),
                $relation->getTargetSetterMethodName(),
                [
                    new Arg(new Variable('this')),
                ]
            )
        );
    }
}
