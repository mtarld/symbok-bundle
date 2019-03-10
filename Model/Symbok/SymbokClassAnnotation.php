<?php

namespace Mtarld\SymbokBundle\Model\Symbok;

class SymbokClassAnnotation
{
    private $realAnnotation;

    public function __construct($realAnnotation)
    {
        $this->realAnnotation = $realAnnotation;
    }

    public function getRealAnnotation()
    {
        return $this->realAnnotation;
    }
}
