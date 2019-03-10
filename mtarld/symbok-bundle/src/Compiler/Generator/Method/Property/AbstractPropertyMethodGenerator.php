<?php

namespace Mtarld\SymbokBundle\Compiler\Generator\Method\Property;

use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedProperty;
use Mtarld\SymbokBundle\Compiler\Generator\Method\AbstractMethodGenerator;
use Mtarld\SymbokBundle\Compiler\Rules\Impl\ClassRules;
use Mtarld\SymbokBundle\Compiler\Rules\Impl\PropertyRules;
use phpDocumentor\Reflection\Types\Context;

abstract class AbstractPropertyMethodGenerator extends AbstractMethodGenerator
{
    /** @var ParsedProperty */
    protected $property;

    /** @var ClassRules */
    protected $classRules;

    /** @var PropertyRules */
    protected $propertyRules;

    public function __construct(
        ParsedProperty $property,
        ClassRules $classRules,
        PropertyRules $propertyRules,
        Context $context
    ) {
        $this->property = $property;
        $this->classRules = $classRules;
        $this->propertyRules = $propertyRules;

        parent::__construct($context);
    }

    protected final function isNullable(): bool
    {
        $propertyMethodSpecificNullable = $this->isMethodRequiresNullable();
        if ($propertyMethodSpecificNullable !== null) {
            return $propertyMethodSpecificNullable;
        }

        $propertySpecificNullable = $this->propertyRules->requiresNullable();
        if ($propertySpecificNullable !== null) {
            return $propertySpecificNullable;
        }

        $classSpecificNullable = $this->classRules->requiresAllPropertiesNullable();
        if ($classSpecificNullable !== null) {
            return $classSpecificNullable;
        }

        return false;
    }

    abstract protected function isMethodRequiresNullable(): ?bool;
}
