<?php

namespace Mtarld\SymbokBundle\Compiler\Generator;

use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedClass;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedProperty;
use Mtarld\SymbokBundle\Compiler\Generator\Method\Class_\Impl\AllArgsConstructor as AllArgsConstructorGenerator;
use Mtarld\SymbokBundle\Compiler\Generator\Method\Class_\Impl\ToString as ToStringGenerator;
use Mtarld\SymbokBundle\Compiler\Generator\Method\Property\Impl\Adder as AdderGenerator;
use Mtarld\SymbokBundle\Compiler\Generator\Method\Property\Impl\Getter as GetterGenerator;
use Mtarld\SymbokBundle\Compiler\Generator\Method\Property\Impl\Remover as RemoverGenerator;
use Mtarld\SymbokBundle\Compiler\Generator\Method\Property\Impl\Setter as SetterGenerator;
use Mtarld\SymbokBundle\Compiler\Rules\Impl\ClassRules;
use Mtarld\SymbokBundle\Compiler\Rules\Impl\PropertyRules;
use Mtarld\SymbokBundle\Compiler\Statements;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\Types\Context;

class Generator
{
    /** @var Context */
    private $context;

    /** @var Serializer */
    private $docBlockSerializer;

    public function __construct(Context $context, Serializer $docBlockSerializer)
    {
        $this->context = $context;
        $this->docBlockSerializer = $docBlockSerializer;
    }

    public function generateGetter(
        ParsedProperty $property,
        ClassRules $classRules,
        PropertyRules $propertyRules
    ): Statements {
        $generator = new GetterGenerator($property, $classRules, $propertyRules, $this->context);

        return $generator->generate();
    }

    public function generateSetter(
        ParsedProperty $property,
        ClassRules $classRules,
        PropertyRules $propertyRules
    ): Statements {
        $generator = new SetterGenerator($property, $classRules, $propertyRules, $this->context);

        return $generator->generate();
    }

    public function generateAdder(
        ParsedProperty $property,
        ClassRules $classRules,
        PropertyRules $propertyRules
    ): Statements {
        $generator = new AdderGenerator($property, $classRules, $propertyRules, $this->context);

        return $generator->generate();
    }

    public function generateRemover(
        ParsedProperty $property,
        ClassRules $classRules,
        PropertyRules $propertyRules
    ): Statements {
        $generator = new RemoverGenerator($property, $classRules, $propertyRules, $this->context);

        return $generator->generate();
    }

    public function generateAllArgsConstructor(ParsedClass $class, ClassRules $classRules): Statements
    {
        $generator = new AllArgsConstructorGenerator($class, $classRules, $this->context);

        return $generator->generate();
    }

    public function generateToString(ParsedClass $class, ClassRules $classRules): Statements
    {
        $generator = new ToStringGenerator($class, $classRules, $this->context);

        return $generator->generate();
    }

    // public function generateEqualTo(string $className, Property ...$properties) : Statements
    // {
    //     $generator = new EqualToGenerator($this->docBlockSerializer);
    //     $generator->setClassName($className);
    //     $generator->setTypeContext($this->typeContext);
    //     $generator->setProperties(...$properties);
    //     return $generator->generate();
    // }
}
