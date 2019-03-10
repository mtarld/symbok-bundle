<?php

namespace Mtarld\SymbokBundle\Factory\Symbok;

use Mtarld\SymbokBundle\Helper\NodesFinder;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClassMethods;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class SymbokClassMethodsFactory
{
    public function create(NodeClass $class): SymbokClassMethods
    {
        $constructor = false;
        $toString = false;

        $classMethods = NodesFinder::findMethods(...$class->stmts);

        foreach ($classMethods as $method) {
            $methodName = $method->name->name;
            if ($methodName == '__construct') {
                $constructor = true;
            }
            if ($methodName == '__toString') {
                $toString = true;
            }
        }

        return new SymbokClassMethods($constructor, $toString);
    }
}
