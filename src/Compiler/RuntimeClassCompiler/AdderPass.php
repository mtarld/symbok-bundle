<?php

namespace Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\AdderBuilder;
use Mtarld\SymbokBundle\Model\Relation\DoctrineCollectionRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\MethodNameGenerator;
use phpDocumentor\Reflection\Types\Array_;

class AdderPass implements PropertyPassInterface
{
    private $behavior;
    private $finder;
    private $builder;

    public function __construct(
        PropertyBehavior $behavior,
        PhpCodeFinder $finder,
        AdderBuilder $builder
    ) {
        $this->behavior = $behavior;
        $this->finder = $finder;
        $this->builder = $builder;
    }

    public function support(SymbokProperty $property): bool
    {
        return
            $this->isCollectionType($property)
            && !$this->finder->hasMethod(
                MethodNameGenerator::generate($property->getName(), MethodNameGenerator::METHOD_ADD),
                $property->getClass()->getStatements()
            )
            && $this->behavior->requireAdder($property)
        ;
    }

    private function isCollectionType(SymbokProperty $property): bool
    {
        return $property->getType() instanceof Array_ || $property->getRelation() instanceof DoctrineCollectionRelation;
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
