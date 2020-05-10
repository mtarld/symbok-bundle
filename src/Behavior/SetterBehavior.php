<?php

namespace Mtarld\SymbokBundle\Behavior;

use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Model\Relation\ManyToOneRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;

/**
 * @internal
 * @final
 */
class SetterBehavior
{
    /** @var PropertyBehavior */
    private $propertyBehavior;

    /** @var array<array-key, mixed> */
    private $defaults;

    /**
     * @param array<mixed> $defaults
     */
    public function __construct(
        PropertyBehavior $propertyBehavior,
        array $defaults
    ) {
        $this->propertyBehavior = $propertyBehavior;
        $this->defaults = $defaults;
    }

    public function isFluent(SymbokProperty $property): bool
    {
        $fluent = $this->isFluentBySetter($property);
        $fluent = is_bool($fluent) ? $fluent : $this->isFluentByData($property);

        return is_bool($fluent) ? $fluent : $this->defaults['fluent'];
    }

    public function isNullable(SymbokProperty $property): bool
    {
        $nullable = $this->isNullableBySetter($property);
        $nullable = is_bool($nullable) ? $nullable : $this->isNullableByDoctrineRelation($property);
        $nullable = is_bool($nullable) ? $nullable : $this->propertyBehavior->isNullable($property);
        $nullable = is_bool($nullable) ? $nullable : $this->isNullableByData($property);

        return is_bool($nullable) ? $nullable : $this->defaults['nullable'];
    }

    public function hasToUpdateOtherSide(SymbokProperty $property): bool
    {
        $updateOtherSide = $this->hasToUpdateOtherSideBySetter($property);
        $updateOtherSide = is_bool($updateOtherSide) ? $updateOtherSide : $this->hasToUpdateOtherSideByData($property);

        return is_bool($updateOtherSide) ? $updateOtherSide : $this->defaults['updateOtherSide'];
    }

    private function isNullableBySetter(SymbokProperty $property): ?bool
    {
        $annotation = $property->getAnnotation(Setter::class);

        return $annotation instanceof Setter ?
            $annotation->nullable
            : null
        ;
    }

    private function isNullableByDoctrineRelation(SymbokProperty $property): ?bool
    {
        if ($property->getRelation() instanceof ManyToOneRelation) {
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

    private function isFluentBySetter(SymbokProperty $property): ?bool
    {
        $annotation = $property->getAnnotation(Setter::class);

        return $annotation instanceof Setter ?
            $annotation->fluent
            : null
        ;
    }

    private function isFluentByData(SymbokProperty $property): ?bool
    {
        $annotation = $property->getAnnotation(Data::class);

        return $annotation instanceof Data ?
            $annotation->fluent
            : null
        ;
    }

    private function hasToUpdateOtherSideBySetter(SymbokProperty $property): ?bool
    {
        $annotation = $property->getAnnotation(Setter::class);

        return $annotation instanceof Setter ?
            $annotation->updateOtherSide
            : null
        ;
    }

    private function hasToUpdateOtherSideByData(SymbokProperty $property): ?bool
    {
        $annotation = $property->getAnnotation(Data::class);

        return $annotation instanceof Data ?
            $annotation->updateOtherSide
            : null
        ;
    }
}
