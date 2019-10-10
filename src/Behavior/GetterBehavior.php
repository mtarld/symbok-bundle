<?php

namespace Mtarld\SymbokBundle\Behavior;

use Doctrine\ORM\Mapping\Column;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use phpDocumentor\Reflection\Types\Boolean;

class GetterBehavior
{
    private $propertyBehavior;
    private $defaults;

    public function __construct(
        PropertyBehavior $propertyBehavior,
        array $config
    ) {
        $this->propertyBehavior = $propertyBehavior;
        $this->defaults = $config['defaults']['getter'];
    }

    public function isNullable(SymbokProperty $property): bool
    {
        $nullable = $this->isNullableByGetter($property);
        $nullable = is_bool($nullable) ? $nullable : $this->isNullableByDoctrineColumn($property);
        $nullable = is_bool($nullable) ? $nullable : $this->propertyBehavior->isNullable($property);
        $nullable = is_bool($nullable) ? $nullable : $this->isNullableByData($property);

        return is_bool($nullable) ? $nullable : $this->defaults['nullable'];
    }

    public function hasHasPrefix(SymbokProperty $property): bool
    {
        $annotation = $property->getAnnotation(Getter::class);

        return
            ($annotation instanceof Getter)
            && $annotation->hasPrefix
            && $property->getType() instanceof Boolean
        ;
    }

    private function isNullableByGetter(SymbokProperty $property): ?bool
    {
        $annotation = $property->getAnnotation(Getter::class);

        return $annotation instanceof Getter ?
            $annotation->nullable
            : null
        ;
    }

    private function isNullableByDoctrineColumn(SymbokProperty $property): ?bool
    {
        // Getter methods always have nullable return values
        // because even though these are required in the db, they may not be set yet
        $annotation = $property->getAnnotation(Column::class);
        if ($annotation instanceof Column) {
            return true;
        }

        return null;
    }

    private function isNullableByData(SymbokProperty $property): ?bool
    {
        $annotation = $property->getAnnotation(Data::class);

        return $annotation instanceof Data ?
            $annotation->nullable
            : null
        ;
    }
}
