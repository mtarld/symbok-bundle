<?php

namespace Mtarld\SymbokBundle\Service;

use Mtarld\SymbokBundle\MethodGenerator\Class_\AllArgsConstructor as AllArgsConstructorGenerator;
use Mtarld\SymbokBundle\MethodGenerator\Class_\ToString as ToStringGenerator;
use Mtarld\SymbokBundle\MethodGenerator\Property\Adder as AdderGenerator;
use Mtarld\SymbokBundle\MethodGenerator\Property\Getter as GetterGenerator;
use Mtarld\SymbokBundle\MethodGenerator\Property\Remover as RemoverGenerator;
use Mtarld\SymbokBundle\MethodGenerator\Property\Setter as SetterGenerator;
use Mtarld\SymbokBundle\Model\Statements;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;

class MethodGeneratorService
{
    /** @var GetterGenerator $getterGenerator */
    private $getterGenerator;

    /** @var SetterGenerator $setterGenerator */
    private $setterGenerator;

    /** @var AdderGenerator $adderGenerator */
    private $adderGenerator;

    /** @var RemoverGenerator $removerGenerator */
    private $removerGenerator;

    /** @var AllArgsConstructorGenerator $allArgsConstructorGenerator */
    private $allArgsConstructorGenerator;

    /** @var ToStringGenerator $toStringGenerator */
    private $toStringGenerator;

    public function __construct(
        GetterGenerator $getterGenerator,
        SetterGenerator $setterGenerator,
        AdderGenerator $adderGenerator,
        RemoverGenerator $removerGenerator,
        AllArgsConstructorGenerator $allArgsConstructorGenerator,
        ToStringGenerator $toStringGenerator
    ) {
        $this->getterGenerator = $getterGenerator;
        $this->setterGenerator = $setterGenerator;
        $this->adderGenerator = $adderGenerator;
        $this->removerGenerator = $removerGenerator;
        $this->allArgsConstructorGenerator = $allArgsConstructorGenerator;
        $this->toStringGenerator = $toStringGenerator;
    }

    public function generateGetter(SymbokProperty $property, SymbokClass $class): Statements
    {
        return $this->getterGenerator->generate($property, $class);
    }

    public function generateSetter(SymbokProperty $property, SymbokClass $class): Statements
    {
        return $this->setterGenerator->generate($property, $class);
    }

    public function generateAdder(SymbokProperty $property, SymbokClass $class): Statements
    {
        return $this->adderGenerator->generate($property, $class);
    }

    public function generateRemover(SymbokProperty $property, SymbokClass $class): Statements
    {
        return $this->removerGenerator->generate($property, $class);
    }

    public function generateAllArgsConstructor(SymbokClass $class): Statements
    {
        return $this->allArgsConstructorGenerator->generate($class);
    }

    public function generateToString(SymbokClass $class): Statements
    {
        return $this->toStringGenerator->generate($class);
    }
}
