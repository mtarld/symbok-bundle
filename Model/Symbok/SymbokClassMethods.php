<?php

namespace Mtarld\SymbokBundle\Model\Symbok;

class SymbokClassMethods
{
    /** @var bool */
    private $constructor;

    /** @var bool */
    private $toString;

    public function __construct(bool $constructor, bool $toString)
    {
        $this->constructor = $constructor;
        $this->toString = $toString;
    }

    public function hasConstructor(): bool
    {
        return $this->constructor;
    }

    public function hasToString(): bool
    {
        return $this->toString;
    }
}
