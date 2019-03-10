<?php

namespace Mtarld\SymbokBundle\Factory\Rules;

use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\Model\Rules\ClassRules;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClassAnnotation;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ClassRulesFactory
{
    /** @var bool */
    private $allArgsConstructor;

    /** @var bool */
    private $allPropertyGetters;

    /** @var bool */
    private $allPropertySetters;

    /** @var bool */
    private $defaultNullable;

    /** @var bool */
    private $fluentSetters;

    /** @var bool */
    private $defaultConstructorNullable;

    /** @var bool */
    private $toString;

    /** @var array */
    private $toStringProperties;

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(SymbokClass $class): ClassRules
    {
        $this->applyDefaults();

        $annotations = $class->getAnnotations();
        foreach ($annotations as $annotation) {
            /** @var SymbokClassAnnotation $annotation */
            switch (get_class($annotation->getRealAnnotation())) {
                case AllArgsConstructor::class:
                    $this->applyAllArgsConstructor($class, $annotation);
                    break;
                case Data::class:
                    $this->applyData($class, $annotation);
                    break;
                case ToString::class:
                    $this->applyToString($class, $annotation);
                    break;
            }
        }

        return new ClassRules(
            $this->allArgsConstructor,
            $this->allPropertyGetters,
            $this->allPropertySetters,
            $this->defaultNullable,
            $this->defaultConstructorNullable,
            $this->fluentSetters,
            $this->toString,
            $this->toStringProperties
        );
    }

    private function applyDefaults(): void
    {
        $symbokDefaults = $this->container->getParameter('symbok')['defaults'];
        $this->defaultNullable = $symbokDefaults['nullable']['getter_setter'];
        $this->defaultConstructorNullable = $symbokDefaults['nullable']['constructor'];
        $this->fluentSetters = $symbokDefaults['fluent_setters'];
        $this->allArgsConstructor = false;
        $this->allPropertyGetters = false;
        $this->allPropertySetters = false;
        $this->toString = false;
        $this->toStringProperties = [];
    }

    private function applyAllArgsConstructor(SymbokClass $class, ?SymbokClassAnnotation $annotation = null): void
    {
        $this->allArgsConstructor = !$class->hasConstructor();
        if ($annotation !== null) {
            /** @var AllArgsConstructor $realAnnotation */
            $realAnnotation = $annotation->getRealAnnotation();
            if ($realAnnotation->nullable !== null) {
                $this->defaultConstructorNullable = $realAnnotation->nullable;
            }
        }
    }

    private function applyData(SymbokClass $class, SymbokClassAnnotation $annotation): void
    {
        /** @var Data $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();

        if ($realAnnotation->nullable !== null) {
            $this->defaultNullable = $realAnnotation->nullable;
        }

        if ($realAnnotation->constructorNullable !== null) {
            $this->defaultConstructorNullable = $realAnnotation->constructorNullable;
        }

        if ($realAnnotation->fluentSetters !== null) {
            $this->fluentSetters = $realAnnotation->fluentSetters;
        }

        $this->allPropertyGetters = true;
        $this->allPropertySetters = true;

        $this->applyAllArgsConstructor($class);
    }

    private function applyToString(SymbokClass $class, SymbokClassAnnotation $annotation): void
    {
        /** @var ToString $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();
        $classProperties = array_map(function (SymbokProperty $classProperty) {
            return $classProperty->getName();
        }, $class->getProperties());

        $toStringProperties = [];
        foreach ($realAnnotation->properties as $property) {
            if (in_array($property, $classProperties)) {
                $toStringProperties[] = $property;
            }
        }

        $this->toString = !$class->hasToString() && sizeof($toStringProperties);
        $this->toStringProperties = $toStringProperties;
    }
}
