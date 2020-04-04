<?php

namespace Mtarld\SymbokBundle\MethodBuilder\AdderBuilder;

use Doctrine\Common\Inflector\Inflector;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;

class RegularStatements
{
    /**
     * @return array<Node>
     */
    public function getStatements(SymbokProperty $property): array
    {
        $propertyName = $property->getName();
        $paramName = Inflector::singularize($propertyName);

        return [
            // $this->props[] = $prop;
            new Expression(
                new Assign(
                    new ArrayDimFetch(
                        new PropertyFetch(
                            new Variable('this'),
                            $propertyName
                        )
                    ),
                    new Variable($paramName)
                )
            ),
        ];
    }
}
