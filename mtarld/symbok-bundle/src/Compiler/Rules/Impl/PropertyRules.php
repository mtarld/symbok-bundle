<?php

namespace Mtarld\SymbokBundle\Compiler\Rules\Impl;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Nullable;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedProperty;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Property\PropertyAnnotation;
use Mtarld\SymbokBundle\Compiler\Rules\RulesInterface;

class PropertyRules implements RulesInterface
{
    /** @var bool */
    private $getter = false;

    /** @var bool */
    private $setter = false;

    /** @var bool */
    private $adder = false;

    /** @var bool */
    private $remover = false;

    /** @var bool|null */
    private $getterNullable;

    /** @var bool|null */
    private $setterNullable;

    /** @var bool|null */
    private $setterFluent;

    /** @var bool|null */
    private $annotationNullable;

    /** @var bool|null */
    private $columnNullable;

    public function __construct(ParsedProperty $property)
    {
        $annotations = $property->getAnnotations();
        foreach ($annotations as $annotation) {
            /** @var PropertyAnnotation $annotation */
            switch (get_class($annotation->getRealAnnotation())) {
                case Getter::class:
                    $this->applyGetter($annotation, $property);
                    break;
                case Setter::class:
                    $this->applySetter($annotation, $property);
                    break;
                case Nullable::class:
                    $this->applyNullable($annotation);
                    break;
                case Column::class:
                    $this->applyOrmColumn($annotation);
                    break;
                case JoinColumn::class:
                    $this->applyOrmColumn($annotation);
                    break;
            }
        }
    }

    private function applyGetter(PropertyAnnotation $annotation, ParsedProperty $property): void
    {
        $this->getter = !$property->hasGetter();

        /** @var Getter $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();
        $annotationSpecificNullable = $realAnnotation->nullable;
        if ($annotationSpecificNullable !== null) {
            $this->getterNullable = $annotationSpecificNullable;
        } else {
            $this->getterNullable = $property->isNullable();
        }
    }

    private function applySetter(PropertyAnnotation $annotation, ParsedProperty $property): void
    {
        $this->setter = !$property->hasSetter();

        /** @var Setter $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();
        $annotationSpecificNullable = $realAnnotation->nullable;
        if ($annotationSpecificNullable !== null) {
            $this->setterNullable = $annotationSpecificNullable;
        } else {
            $this->setterNullable = $property->isNullable();
        }

        $annotationSpecificFluent = $realAnnotation->fluent;
        if ($annotationSpecificFluent !== null) {
            $this->setterFluent = $annotationSpecificFluent;
        }

        $this->applyAdder($property);
        $this->applyRemover($property);
    }

    private function applyAdder(ParsedProperty $property): void
    {
        if ($this->isDoctrineCollectionProperty($property)) {
            $this->adder = !$property->hasAdder() && $property->canUseAdder();
        }
    }

    private function isDoctrineCollectionProperty(ParsedProperty $property): bool
    {
        $doctrineAnnotation = $property->getDoctrineRelationAnnotation();
        if ($doctrineAnnotation) {
            return $doctrineAnnotation->isDoctrineCollectionAnnotation();
        }

        return false;
    }

    private function applyRemover(ParsedProperty $property): void
    {
        if ($this->isDoctrineCollectionProperty($property)) {
            $this->remover = !$property->hasRemover() && $property->canUseRemover();
        }
    }

    private function applyNullable(PropertyAnnotation $annotation): void
    {
        /** @var Nullable $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();
        $this->annotationNullable = $realAnnotation->nullable;
    }

    private function applyOrmColumn(PropertyAnnotation $annotation): void
    {
        /** @var Column $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();
        $this->columnNullable = $realAnnotation->nullable;
    }

    public function requiresGetter(): bool
    {
        return $this->getter;
    }

    public function requiresSetter(): bool
    {
        return $this->setter;
    }

    public function requiresAdder(): bool
    {
        return $this->adder;
    }

    public function requiresRemover(): bool
    {
        return $this->remover;
    }

    public function requiresGetterNullable(): ?bool
    {
        return $this->getterNullable;
    }

    public function requiresSetterNullable(): ?bool
    {
        return $this->setterNullable;
    }

    public function requiresNullable(): ?bool
    {
        if ($this->annotationNullable !== null) {
            return $this->annotationNullable;
        }

        return $this->columnNullable;
    }

    public function requiresSetterFluent(): ?bool
    {
        return $this->setterFluent;
    }
}
