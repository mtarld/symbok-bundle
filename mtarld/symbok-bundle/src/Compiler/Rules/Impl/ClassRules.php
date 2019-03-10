<?php

namespace Mtarld\SymbokBundle\Compiler\Rules\Impl;

use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Class_\ClassAnnotation;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedClass;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedProperty;
use Mtarld\SymbokBundle\Compiler\Rules\RulesInterface;

class ClassRules implements RulesInterface
{
    /** @var bool */
    private $allArgsConstructor = false;

    /** @var bool */
    private $allPropertyGetters;

    /** @var bool */
    private $allPropertySetters;

    /** @var bool */
    private $defaultNullable;

    /** @var bool */
    private $fluentSetters = false;

    /** @var bool */
    private $defaultConstructorNullable = true;

    /** @var bool */
    private $equalTo;

    /** @var bool */
    private $toString;

    /** @var array */
    private $toStringProperties = [];

    public function __construct(ParsedClass $class)
    {
        $annotations = $class->getAnnotations();
        foreach ($annotations as $annotation) {
            /** @var ClassAnnotation $annotation */
            switch (get_class($annotation->getRealAnnotation())) {
                case AllArgsConstructor::class:
                    $this->applyAllArgsConstructor($class, $annotation);
                    break;
                case Data::class:
                    $this->applyData($annotation, $class);
                    break;
                case ToString::class:
                    $this->applyToString($annotation, $class);
                    break;
            }
        }
    }

    private function applyAllArgsConstructor(ParsedClass $class, ?ClassAnnotation $annotation = null)
    {
        $this->allArgsConstructor = !$class->hasConstructor();
        if ($annotation !== null) {
            /** @var AllArgsConstructor $realAnnotation */
            $realAnnotation = $annotation->getRealAnnotation();
            $this->defaultConstructorNullable = $realAnnotation->nullable;
        }
    }

    private function applyData(ClassAnnotation $annotation, ParsedClass $class): void
    {
        /** @var Data $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();

        $this->defaultNullable = $realAnnotation->nullable;
        $this->defaultConstructorNullable = $realAnnotation->constructorNullable !== null ? $realAnnotation->constructorNullable : $realAnnotation->nullable;
        $this->fluentSetters = $realAnnotation->fluentSetters;
        $this->allPropertyGetters = true;
        $this->allPropertySetters = true;

        $this->applyAllArgsConstructor($class);
    }

    private function applyToString(ClassAnnotation $annotation, ParsedClass $class): void
    {
        /** @var ToString $realAnnotation */
        $realAnnotation = $annotation->getRealAnnotation();
        $classProperties = array_map(function (ParsedProperty $classProperty) {
            return $classProperty->getName();
        }, $class->getProperties());

        $toStringProperties = [];
        foreach ($realAnnotation->properties as $property) {
            if (in_array($property, $classProperties)) {
                $toStringProperties[] = $property;
            }
        }

        $this->toString = true && sizeof($toStringProperties);
        $this->toStringProperties = $toStringProperties;
    }

    public function requiresAllArgsConstructor(): bool
    {
        return $this->allArgsConstructor;
    }

    public function requiresAllPropertyGetters(): ?bool
    {
        return $this->allPropertyGetters;
    }

    public function requiresAllPropertySetters(): ?bool
    {
        return $this->allPropertySetters;
    }

    public function requiresAllPropertiesNullable(): ?bool
    {
        return $this->defaultNullable;
    }

    public function requiresFluentSetters(): bool
    {
        return $this->fluentSetters;
    }

    public function requiresConstructorNullable(): bool
    {
        return $this->defaultConstructorNullable;
    }

    public function requiresEqualTo(): bool
    {
        return $this->equalTo;
    }

    public function requiresToString(): bool
    {
        return $this->toString;
    }

    public function getToStringProperties(): array
    {
        return $this->toStringProperties;
    }
}
