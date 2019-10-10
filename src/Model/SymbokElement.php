<?php

namespace Mtarld\SymbokBundle\Model;

abstract class SymbokElement
{
    protected $annotations;

    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    public function getAnnotation(string $annotationClass)
    {
        foreach ($this->getAnnotations() as $annotation) {
            if ($annotation instanceof $annotationClass) {
                return $annotation;
            }
        }

        return null;
    }
}
