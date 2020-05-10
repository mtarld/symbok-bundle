<?php

namespace Mtarld\SymbokBundle\Behavior;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Nullable;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Model\Relation\DoctrineCollectionRelation;
use Mtarld\SymbokBundle\Model\Relation\DoctrineSingleRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;

/**
 * @internal
 * @final
 */
class PropertyBehavior
{
    public function isNullable(SymbokProperty $property): ?bool
    {
        $nullable = $this->isNullableByNullableAnnotation($property);
        $nullable = is_bool($nullable) ? $nullable : $this->isNullableByDoctrineColumn($property);

        return is_bool($nullable) ? $nullable : $this->isNullableByDoctrineRelation($property);
    }

    public function requireGetter(SymbokProperty $property): bool
    {
        $annotation = $property->getAnnotation(Getter::class) ?? $property->getClass()->getAnnotation(Data::class);

        return $annotation instanceof Getter || $annotation instanceof Data;
    }

    public function requireSetter(SymbokProperty $property): bool
    {
        $annotation = $property->getAnnotation(Setter::class) ?? $property->getClass()->getAnnotation(Data::class);

        return $annotation instanceof Setter || $annotation instanceof Data;
    }

    public function requireAdder(SymbokProperty $property): bool
    {
        /** @var Setter|Data $annotation */
        $annotation = $property->getAnnotation(Setter::class) ?? $property->getClass()->getAnnotation(Data::class);

        return $this->requireSetter($property) && true === $annotation->add;
    }

    public function requireRemover(SymbokProperty $property): bool
    {
        /** @var Setter|Data $annotation */
        $annotation = $property->getAnnotation(Setter::class) ?? $property->getClass()->getAnnotation(Data::class);

        return $this->requireSetter($property) && true === $annotation->remove;
    }

    private function isNullableByNullableAnnotation(SymbokProperty $property): ?bool
    {
        $annotation = $property->getAnnotation(Nullable::class);

        return $annotation instanceof Nullable
            ? $annotation->nullable
            : null
        ;
    }

    private function isNullableByDoctrineColumn(SymbokProperty $property): ?bool
    {
        $annotation = $property->getAnnotation(Column::class) ?: $property->getAnnotation(JoinColumn::class);

        return $annotation instanceof Column || $annotation instanceof JoinColumn
            ? $annotation->nullable
            : null
        ;
    }

    private function isNullableByDoctrineRelation(SymbokProperty $property): ?bool
    {
        $relation = $property->getRelation();
        if ($relation instanceof DoctrineSingleRelation) {
            return true;
        }
        if ($relation instanceof DoctrineCollectionRelation) {
            return false;
        }

        return null;
    }
}
