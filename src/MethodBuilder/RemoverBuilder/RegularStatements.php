<?php

namespace Mtarld\SymbokBundle\MethodBuilder\RemoverBuilder;

use Doctrine\Common\Inflector\Inflector;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;

class RegularStatements
{
    public function getStatements(SymbokProperty $property): array
    {
        $propertyName = $property->getName();
        $paramName = Inflector::singularize($propertyName);

        return [
            $this->getSearchKeyStmt($propertyName, $paramName),
            new If_(
                new NotIdentical(new ConstFetch(new Name('false')), new Variable('key')),
                [
                    'stmts' => [
                        $this->getUnsetStmt($propertyName),
                    ],
                ]
            ),
        ];
    }

    private function getSearchKeyStmt(string $propertyName, string $paramName): Stmt
    {
        return new Expression(
            new Assign(
                new Variable('key'),
                new FuncCall(
                    new Name('array_search'),
                    [
                        new Arg(new Variable($paramName)),
                        new Arg(new PropertyFetch(new Variable('this'), $propertyName)),
                        new Arg(new ConstFetch(new Name('true'))),
                    ]
                )
            )
        );
    }

    private function getUnsetStmt(string $propertyName): Stmt
    {
        return new Expression(
            new FuncCall(
                new Name('unset'),
                [
                    new Arg(
                        new ArrayDimFetch(
                            new PropertyFetch(new Variable('this'), $propertyName),
                            new Variable('key')
                        )
                    ),
                ]
            )
        );
    }
}
