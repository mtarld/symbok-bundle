<?php

namespace Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\ClassBehavior;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\ConstructorBuilder;
use Mtarld\SymbokBundle\Model\Relation\DoctrineCollectionRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;

class ConstructorPass implements ClassPassInterface
{
    private $behavior;
    private $finder;
    private $builder;

    public function __construct(
        ClassBehavior $behavior,
        PhpCodeFinder $finder,
        ConstructorBuilder $builder
    ) {
        $this->behavior = $behavior;
        $this->finder = $finder;
        $this->builder = $builder;
    }

    public function support(SymbokClass $class): bool
    {
        return !$this->finder->hasMethod(
            '__construct',
            $class->getStatements()
        )
            && !$this->behavior->requireAllArgsConstructor($class)
            && $this->hasDoctrineCollectionRelations($class)
        ;
    }

    private function hasDoctrineCollectionRelations(SymbokClass $class): bool
    {
        return array_reduce($class->getProperties(), function (bool $result, SymbokProperty $property) {
            return $result || $property->getRelation() instanceof DoctrineCollectionRelation;
        }, false);
    }

    public function process(SymbokClass $class): SymbokClass
    {
        return $class->setStatements(array_merge(
            $class->getStatements(),
            [$this->builder->build($class)]
        ));
    }
}
