<?php

namespace Mtarld\SymbokBundle\Factory\Symbok;

use Mtarld\SymbokBundle\Factory\Rules\ClassRulesFactory;
use Mtarld\SymbokBundle\Helper\NodesFinder;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class SymbokClassFactory
{
    /** @var SymbokClassAnnotationsFactory */
    private $annotationFactory;

    /** @var SymbokClassMethodsFactory */
    private $methodsFactory;

    /** @var SymbokPropertyFactory */
    private $propertyFactory;

    /** @var SymbokClassDocBlockFactory */
    private $docBlockFactory;

    /** @var ClassRulesFactory */
    private $classRulesFactory;

    public function __construct(
        SymbokClassAnnotationsFactory $annotationsParser,
        SymbokClassMethodsFactory $methodsParser,
        SymbokPropertyFactory $propertyFactory,
        SymbokClassDocBlockFactory $docBlockParser,
        ClassRulesFactory $classRulesFactory
    ) {
        $this->annotationFactory = $annotationsParser;
        $this->methodsFactory = $methodsParser;
        $this->propertyFactory = $propertyFactory;
        $this->docBlockFactory = $docBlockParser;
        $this->classRulesFactory = $classRulesFactory;
    }

    public function create($class): SymbokClass
    {
        /** @var NodeClass $class */
        $classProperties = NodesFinder::findProperties(...$class->stmts);
        $properties = [];

        foreach ($classProperties as $property) {
            $properties[] = $this->propertyFactory->create($class, $property);
        }

        $parsedClass = new SymbokClass(
            $class->name->name,
            $this->annotationFactory->create($class),
            $this->docBlockFactory->create($class),
            $properties,
            $this->methodsFactory->create($class)
        );

        $rules = $this->classRulesFactory->create($parsedClass);
        $parsedClass->setRules($rules);

        return $parsedClass;
    }
}
