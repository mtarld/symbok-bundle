<?php

namespace Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\SetterBuilder;
use Mtarld\SymbokBundle\Model\Relation\DoctrineCollectionRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\MethodNameGenerator;

class SetterPass implements PropertyPassInterface
{
    private $behavior;
    private $finder;
    private $builder;

    public function __construct(
        PropertyBehavior $behavior,
        PhpCodeFinder $finder,
        SetterBuilder $builder
    ) {
        $this->behavior = $behavior;
        $this->finder = $finder;
        $this->builder = $builder;
    }

    public function support(SymbokProperty $property): bool
    {
        return !$this->finder->hasMethod(
            MethodNameGenerator::generate($property->getName(), MethodNameGenerator::METHOD_SET),
            $property->getClass()->getStatements()
        )
            && $this->behavior->requireSetter($property)
            && !$property->getRelation() instanceof DoctrineCollectionRelation
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
