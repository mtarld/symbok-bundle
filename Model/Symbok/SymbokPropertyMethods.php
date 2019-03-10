<?php

namespace Mtarld\SymbokBundle\Model\Symbok;

class SymbokPropertyMethods
{
    /** @var bool */
    private $getter;

    /** @var bool */
    private $setter;

    /** @var bool */
    private $adder;

    /** @var bool */
    private $remover;

    public function __construct(
        bool $getter,
        bool $setter,
        bool $adder,
        bool $remover
    ) {
        $this->getter = $getter;
        $this->setter = $setter;
        $this->adder = $adder;
        $this->remover = $remover;
    }

    public function hasGetter(): bool
    {
        return $this->getter;
    }

    public function hasSetter(): bool
    {
        return $this->setter;
    }

    public function hasAdder(): bool
    {
        return $this->adder;
    }

    public function hasRemover(): bool
    {
        return $this->remover;
    }
}
