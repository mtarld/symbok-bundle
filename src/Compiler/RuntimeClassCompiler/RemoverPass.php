<?php

namespace Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\RemoverBuilder;
use Mtarld\SymbokBundle\Model\Relation\DoctrineCollectionRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\MethodNameGenerator;
use phpDocumentor\Reflection\Types\Array_;

/**
 * @internal
 * @final
 */
class RemoverPass implements PropertyPassInterface
{
    /** @var PropertyBehavior */
    private $behavior;

    /** @var PhpCodeFinder */
    private $finder;

    /** @var RemoverBuilder */
    private $builder;

    public function __construct(
        PropertyBehavior $behavior,
        PhpCodeFinder $finder,
        RemoverBuilder $builder
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
                MethodNameGenerator::generate($property->getName(), MethodNameGenerator::METHOD_REMOVE),
                $property->getClass()->getStatements()
            )
            && $this->behavior->requireRemover($property)
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
