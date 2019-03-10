<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Class_;

use Mtarld\SymbokBundle\Compiler\Code\Parsed\Class_\ClassMethods;
use Mtarld\SymbokBundle\Compiler\Helper\NodeFinder;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class MethodsParser
{
    /** @var array */
    private $classMethods;

    public function __construct(NodeClass $class)
    {
        $this->classMethods = NodeFinder::findMethods(...$class->stmts);
    }

    public function parse(): ClassMethods
    {
        $constructor = false;
        $toString = false;

        foreach ($this->classMethods as $method) {
            $methodName = $method->name->name;
            if ($methodName == '__construct') {
                $constructor = true;
            }
            if ($methodName == '__toString') {
                $toString = true;
            }
        }

        return new ClassMethods($constructor, $toString);
    }
}
