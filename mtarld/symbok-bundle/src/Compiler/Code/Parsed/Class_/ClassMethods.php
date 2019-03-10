<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parsed\Class_;

class ClassMethods
{
    /** @var bool */
    private $constructor;

    /** @var bool */
    private $toString;

    public function __construct(bool $constructor, bool $toString)
    {
        $this->constructor = $constructor;
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

