<?php

namespace Mtarld\SymbokBundle\Model\Rules;

class ClassRules
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

    public function __construct(
        bool $allArgsConstructor,
        bool $allPropertyGetters,
        bool $allPropertySetters,
        bool $defaultNullable,
        bool $defaultConstructorNullable,
        bool $fluentSetters,
        bool $toString,
        array $toStringProperties
    ) {
        $this->allArgsConstructor = $allArgsConstructor;
        $this->allPropertyGetters = $allPropertyGetters;
        $this->allPropertySetters = $allPropertySetters;
        $this->defaultNullable = $defaultNullable;
        $this->defaultConstructorNullable = $defaultConstructorNullable;
        $this->fluentSetters = $fluentSetters;
        $this->toString = $toString;
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

    public function requiresToString(): bool
    {
        return $this->toString;
    }

    public function getToStringProperties(): array
    {
        return $this->toStringProperties;
    }
}
