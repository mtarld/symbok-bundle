<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parsed\Class_;

class ClassAnnotation
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
