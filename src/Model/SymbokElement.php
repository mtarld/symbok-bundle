<?php

namespace Mtarld\SymbokBundle\Model;

abstract class SymbokElement
{
    /** @var array<mixed> */
    protected $annotations;

    public function __construct(array $annotations)
    {
        $this->annotations = $annotations;
    }

    /**
     * @return array<mixed>
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    /**
     * @return mixed|null
     */
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
