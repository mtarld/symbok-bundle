<?php

namespace Mtarld\SymbokBundle\Factory\Symbok;

use Mtarld\SymbokBundle\Factory\Rules\PropertyRulesFactory;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
use PhpParser\Node\Stmt\Class_ as NodeClass;
use PhpParser\Node\Stmt\Property;

class SymbokPropertyFactory
{
    /** @var SymbokPropertyAnnotationsFactory */
    private $annotationFactory;

    /** @var SymbokPropertyMethodsFactory */
    private $methodsFactory;

    /** @var SymbokPropertyTypesFactory */
    private $typesFactory;

    /** @var PropertyRulesFactory */
    private $rulesFactory;

    public function __construct(
        SymbokPropertyMethodsFactory $methodsFactory,
        SymbokPropertyAnnotationsFactory $propertyAnnotationFactory,
        SymbokPropertyTypesFactory $typesFactory,
        PropertyRulesFactory $rulesFactory
    ) {
        $this->annotationFactory = $propertyAnnotationFactory;
        $this->methodsFactory = $methodsFactory;
        $this->typesFactory = $typesFactory;
        $this->rulesFactory = $rulesFactory;
    }

    public function create(NodeClass $class, Property $property): SymbokProperty
    {
        $annotations = $this->annotationFactory->create($property);
        $types = $this->typesFactory->create($property, $annotations);
        $methods = $this->methodsFactory->create($class, $property->props[0], $types);


        $symbokProperty = new SymbokProperty(
            $property->props[0]->name->name,
            $types,
            $methods,
            $annotations
        );
        $rules = $this->rulesFactory->create($symbokProperty);
        $symbokProperty->setRules($rules);

        return $symbokProperty;
    }
}
