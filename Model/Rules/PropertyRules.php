<?php

namespace Mtarld\SymbokBundle\Model\Rules;

class PropertyRules
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

    public function __construct(
        bool $getter,
        bool $setter,
        bool $adder,
        bool $remover,
        ?bool $getterNullable,
        ?bool $setterNullable,
        ?bool $setterFluent,
        ?bool $annotationNullable,
        ?bool $columnNullable
    ) {
        $this->getter = $getter;
        $this->setter = $setter;
        $this->adder = $adder;
        $this->remover = $remover;
        $this->getterNullable = $getterNullable;
        $this->setterNullable = $setterNullable;
        $this->setterFluent = $setterFluent;
        $this->annotationNullable = $annotationNullable;
        $this->columnNullable = $columnNullable;
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
