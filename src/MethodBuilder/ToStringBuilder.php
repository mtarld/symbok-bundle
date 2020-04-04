<?php

namespace Mtarld\SymbokBundle\MethodBuilder;

use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\Model\SymbokClass;
use phpDocumentor\Reflection\Types\String_;
use PhpParser\Builder\Method;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;

class ToStringBuilder
{
    public function build(SymbokClass $class): ClassMethod
    {
        $methodBuilder = (new Method('__toString'))
                       ->makePublic()
                       ->setReturnType((string) new String_())
        ;

        /** @var ToString $toString */
        $toString = $class->getAnnotation(ToString::class);

        // return (string) ('Class: ' . ($this->propA . (', ' . $this->propB)));

        $properties = $toString->properties;
        $statements = new PropertyFetch(
            new Variable('this'),
            (string) array_shift($properties)
        );

        $statements = array_reduce($properties, static function (Expr $result, string $property): Concat {
            return new Concat(
                $result,
                new Concat(
                    new ConstFetch(new Name("', '")),
                    new PropertyFetch(
                        new Variable('this'),
                        $property
                    )
                )
            );
        }, $statements);

        $methodBuilder->addStmt(
            new Return_(
                new Cast\String_(
                    new Concat(
                        new ConstFetch(new Name('\''.$class->getName().': \'')),
                        $statements
                    )
                )
            )
        );

        return $methodBuilder->getNode();
    }
}
