<?php

namespace Mtarld\SymbokBundle\Compiler\Compiler;

use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedClass;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedProperty;
use Mtarld\SymbokBundle\Compiler\Generator\Generator;
use Mtarld\SymbokBundle\Compiler\Rules\Impl\ClassRules;
use Mtarld\SymbokBundle\Compiler\Rules\Impl\PropertyRules;
use Mtarld\SymbokBundle\Compiler\Statements;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\Types\Context;

class PropertiesCompiler
{
    public function compile(
        ParsedClass $class,
        Context $context,
        Statements $statements,
        ClassRules $classRules
    ): void {
        $generator = new Generator($context, new Serializer());
        $properties = $class->getProperties();

        foreach ($properties as $property) {
            /** @var ParsedProperty $property */
            $propertyRules = new PropertyRules($property);
            if ($classRules->requiresAllPropertyGetters()) {
                $statements->merge($generator->generateGetter($property, $classRules, $propertyRules));
            }
            if ($classRules->requiresAllPropertySetters()) {
                $statements->merge($generator->generateSetter($property, $classRules, $propertyRules));
            }
            if ($propertyRules->requiresGetter() && !$classRules->requiresAllPropertyGetters()) {
                $statements->merge($generator->generateGetter($property, $classRules, $propertyRules));
            }
            if (!$classRules->requiresAllPropertySetters()) {
                if ($propertyRules->requiresSetter()) {
                    $statements->merge($generator->generateSetter($property, $classRules, $propertyRules));
                }
                if ($propertyRules->requiresAdder()) {
                    $statements->merge($generator->generateAdder($property, $classRules, $propertyRules));
                }
                if ($propertyRules->requiresRemover()) {
                    $statements->merge($generator->generateRemover($property, $classRules, $propertyRules));
                }
            }
            // if ($classContext->requiresToString($property)) {
            //     $statements->merge($generatorFactory->generateToString($property->getName()));
            // }
        }
    }
}
