<?php

namespace Mtarld\SymbokBundle\MethodBuilder\RemoverBuilder;

use Mtarld\SymbokBundle\Behavior\SetterBehavior;
use Mtarld\SymbokBundle\Model\Relation\DoctrineRelation;
use Mtarld\SymbokBundle\Model\Relation\ManyToManyRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\Inflector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
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

        $ifStatements = [$this->getOwnSideUpdateStmt($propertyName, $paramName)];

        if ($this->behavior->hasToUpdateOtherSide($property)) {
            /** @var DoctrineRelation $relation */
            $relation = $property->getRelation();
            if ($relation->isOwning()) {
                $ifStatements[] = $this->getOtherSideUpdateStmt($relation, $paramName);
            }
        }

        return [
            // if ($this->cars->contains($car)) {
            new If_(
                new MethodCall(
                    new PropertyFetch(
                        new Variable('this'),
                        $propertyName
                    ),
                    'contains',
                    [
                        new Arg(new Variable($paramName)),
                    ]
                ),
                [
                    'stmts' => $ifStatements,
                ]
            ),
        ];
    }

    private function getOwnSideUpdateStmt(string $propertyName, string $paramName): Stmt
    {
        // $this->cars->removeElement($car);
        return new Expression(
            new MethodCall(
                new PropertyFetch(
                    new Variable('this'),
                    $propertyName
                ),
                'removeElement',
                [
                    new Arg(new Variable($paramName)),
                ]
            )
        );
    }

    private function getOtherSideUpdateStmt(DoctrineRelation $relation, string $paramName): Stmt
    {
        if ($relation instanceof ManyToManyRelation) {
            //  $car->removeUser($this);
            return new Expression(
                new MethodCall(
                    new Variable($paramName),
                    $relation->getTargetRemoverMethodName(),
                    [
                        new Arg(new Variable('this')),
                    ]
                )
            );
        }

        // if ($car->getUser() === $this) {
        return new If_(
            new Identical(
                new MethodCall(
                    new Variable($paramName),
                    $relation->getTargetGetterMethodName()
                ),
                new Variable('this')
            ),
            [
                'stmts' => [
                    //  $car->setUser(null);
                    new Expression(
                        new MethodCall(
                            new Variable($paramName),
                            $relation->getTargetSetterMethodName(),
                            [
                                new Arg(new ConstFetch(new Name('null'))),
                            ]
                        )
                    ),
                ],
            ]
        );
    }
}
