<?php

namespace Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\ClassBehavior;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\ToStringBuilder;
use Mtarld\SymbokBundle\Model\SymbokClass;

class ToStringPass implements ClassPassInterface
{
    /** @var ClassBehavior */
    private $behavior;

    /** @var PhpCodeFinder */
    private $finder;

    /** @var ToStringBuilder */
    private $builder;

    public function __construct(
        ClassBehavior $behavior,
        PhpCodeFinder $finder,
        ToStringBuilder $builder
    ) {
        $this->behavior = $behavior;
        $this->finder = $finder;
        $this->builder = $builder;
    }

    public function support(SymbokClass $class): bool
    {
        return !$this->finder->hasMethod('__toString', $class->getStatements())
            && $this->behavior->requireToString($class)
        ;
    }

    public function process(SymbokClass $class): SymbokClass
    {
        return $class->setStatements(array_merge(
            $class->getStatements(),
            [$this->builder->build($class)]
        ));
    }
}
