<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parsed\Class_;

class ClassMethods
{
    /** @var bool */
    private $constructor;

    public function __construct(bool $constructor)
    {
        $this->constructor = $constructor;
    }

    public function hasConstructor(): bool
    {
        return $this->constructor;
    }
}

