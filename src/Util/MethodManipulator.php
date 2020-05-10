<?php

namespace Mtarld\SymbokBundle\Util;

use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Void_;
use PhpParser\Builder\Method;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;

/**
 * @internal
 * @final
 */
class MethodManipulator
{
    public function makeFluent(Method $builder): void
    {
        $builder->addStmt(new Return_(new Variable('this')));
        $builder->setReturnType((string) new Self_());
    }

    public function makeVoidReturn(Method $builder): void
    {
        if (PHP_VERSION_ID >= 70100) {
            $builder->setReturnType((string) new Void_());
        }
    }
}
