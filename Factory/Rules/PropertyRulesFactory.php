<?php

namespace Mtarld\SymbokBundle\Factory\Rules;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Nullable;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Model\Rules\PropertyRules;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyAnnotation;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PropertyRulesFactory
{
    /** @var bool */
    private $getter;

    /** @var bool */
    private $setter;

    /** @var bool */
    private $adder;

    /** @var bool */
    private $remover;

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

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(SymbokProperty $property): PropertyRules
    {
        $this->applyDefaults();

        $annotations = $property->getAnnotations();
        foreach ($annotations as $annotation) {
            /** @var SymbokPropertyAnnotation $annotation */
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

        return new PropertyRules(
            $this->getter,
            $this->setter,
            $this->adder,
            $this->remover,
            $this->getterNullable,
            $this->setterNullable,
            $this->setterFluent,
            $this->annotationNullable,
            $this->columnNullable
        );
    }

    private function applyDefaults(): void
    {
        $this->getter = false;
        $this->setter = false;
        $this->adder = false;
        $this->remover = false;
        $this->getterNullable = null;
        $this->setterNullable = null;
        $this->setterFluent = null;
        $this->annotationNullable = null;
        $this->columnNullable = null;
    }

    private function applyGetter(SymbokPropertyAnnotation $annotation, SymbokProperty $property): void
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

    private function applySetter(SymbokPropertyAnnotation $annotation, SymbokProperty $property): void
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

    private function applyAdder(SymbokProperty $property): void
    {
        if ($this->isDoctrineCollectionProperty($property)) {
            $this->adder = !$property->hasAdder() && $property->canUseAdder();
        }
    }

    private function isDoctrineCollectionProperty(SymbokProperty $property): bool
    {
        $doctrineAnnotation = $property->getDoctrineRelationAnnotation();
        if ($doctrineAnnotation) {
            return $doctrineAnnotation->isDoctrineCollectionAnnotation();
        }

        return false;
    }

    private function applyRemover(SymbokProperty $property): void
    {
        if ($this->isDoctrineCollectionProperty($property)) {
            $this->remover = !$property->hasRemover() && $property->canUseRemover();
        }
    }

    private function applyNullable(SymbokPropertyAnnotation $annotation): void
    {
        /** @var Nullable $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();
        $this->annotationNullable = $realAnnotation->nullable;
    }

    private function applyOrmColumn(SymbokPropertyAnnotation $annotation): void
    {
        /** @var Column $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();
        $this->columnNullable = $realAnnotation->nullable;
    }
}
