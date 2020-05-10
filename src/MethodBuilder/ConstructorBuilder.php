<?php

namespace Mtarld\SymbokBundle\MethodBuilder;

use Doctrine\Common\Collections\ArrayCollection;
use Mtarld\SymbokBundle\Model\Relation\DoctrineCollectionRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PhpParser\Builder\Method;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;

/**
 * @internal
 * @final
 */
class ConstructorBuilder
{
    public function build(SymbokClass $class): ClassMethod
    {
        $methodBuilder = (new Method('__construct'))->makePublic();

        $collectionProperties = array_filter($class->getProperties(), static function (SymbokProperty $property): bool {
            return $property->getRelation() instanceof DoctrineCollectionRelation;
        });
        $statements = array_map(static function (SymbokProperty $property): Expression {
            return new Expression(
                new Assign(
                    new PropertyFetch(
                        new Variable('this'),
                        $property->getName()
                    ),
                    new New_(new Name(ArrayCollection::class))
                )
            );
        }, $collectionProperties);

        $methodBuilder->addStmts($statements);

        return $methodBuilder->getNode();
    }
}
