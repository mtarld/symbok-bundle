<?php

namespace Mtarld\SymbokBundle\Behavior;

use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Model\SymbokProperty;

class AllArgsConstructorBehavior
{
    private $propertyBehavior;
    private $defaults;

    public function __construct(
        PropertyBehavior $propertyBehavior,
        array $config
    ) {
        $this->propertyBehavior = $propertyBehavior;
        $this->defaults = $config['defaults']['constructor'];
    }

    public function isNullable(SymbokProperty $property): bool
    {
        $nullable = $this->isNullableByAllArgsConstructor($property);
        $nullable = is_bool($nullable) ? $nullable : $this->isNullableByData($property);
        $nullable = is_bool($nullable) ? $nullable : $this->propertyBehavior->isNullable($property);

        return is_bool($nullable) ? $nullable : $this->defaults['nullable'];
    }

    private function isNullableByAllArgsConstructor(SymbokProperty $property): ?bool
    {
        $annotation = $property->getClass()->getAnnotation(AllArgsConstructor::class);

        return $annotation instanceof AllArgsConstructor ?
            $annotation->nullable
            : null
        ;
    }

    private function isNullableByData(SymbokProperty $property): ?bool
    {
        $annotation = $property->getClass()->getAnnotation(Data::class);

        return $annotation instanceof Data ?
            $annotation->constructorNullable
            : null
        ;
    }
}
