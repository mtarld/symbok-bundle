<?php

namespace Mtarld\SymbokBundle\Compiler;

use Mtarld\SymbokBundle\Model\Statements;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
use Mtarld\SymbokBundle\Service\MethodGeneratorService;

class PropertyCompiler
{
    /** @var MethodGeneratorService */
    private $methodGeneratorService;

    public function __construct(MethodGeneratorService $methodGeneratorService)
    {
        $this->methodGeneratorService = $methodGeneratorService;
    }

    public function compile(SymbokClass $class, SymbokProperty $property, Statements $statements): void
    {
        $classRules = $class->getRules();
        /** @var SymbokProperty $property */
        $propertyRules = $property->getRules();
        if ($classRules->requiresAllPropertyGetters()) {
            if (!$property->hasGetter()) {
                $statements->merge($this->methodGeneratorService->generateGetter($property, $class));
            }
        }

        if ($classRules->requiresAllPropertySetters()) {
            if (!$property->hasSetter()) {
                $statements->merge($this->methodGeneratorService->generateSetter($property, $class));
            }
        }

        if ($propertyRules->requiresGetter() && !$classRules->requiresAllPropertyGetters()) {
            $statements->merge($this->methodGeneratorService->generateGetter($property, $class));
        }

        if (!$classRules->requiresAllPropertySetters()) {
            if ($propertyRules->requiresSetter()) {
                $statements->merge($this->methodGeneratorService->generateSetter($property, $class));
            }
            if ($propertyRules->requiresAdder()) {
                $statements->merge($this->methodGeneratorService->generateAdder($property, $class));
            }
            if ($propertyRules->requiresRemover()) {
                $statements->merge($this->methodGeneratorService->generateRemover($property, $class));
            }
        }
    }
}
