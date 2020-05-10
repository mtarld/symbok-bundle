<?php

namespace Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\GetterBuilder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\MethodNameGenerator;

/**
 * @internal
 * @final
 */
class GetterPass implements PropertyPassInterface
{
    /** @var PropertyBehavior */
    private $behavior;

    /** @var PhpCodeFinder */
    private $finder;

    /** @var GetterBuilder */
    private $builder;

    public function __construct(
        PropertyBehavior $behavior,
        PhpCodeFinder $finder,
        GetterBuilder $builder
    ) {
        $this->behavior = $behavior;
        $this->finder = $finder;
        $this->builder = $builder;
    }

    public function support(SymbokProperty $property): bool
    {
        return !$this->finder->hasMethod(
            MethodNameGenerator::generate($property->getName(), MethodNameGenerator::METHOD_GET),
            $property->getClass()->getStatements()
        )
            && $this->behavior->requireGetter($property)
        ;
    }

    public function process(SymbokProperty $property): SymbokClass
    {
        $class = $property->getClass();

        return $class->setStatements(array_merge(
            $class->getStatements(),
            [$this->builder->build($property)]
        ));
    }
}
