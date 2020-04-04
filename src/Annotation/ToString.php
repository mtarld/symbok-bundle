<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class ToString implements AnnotationInterface
{
    /** @var array<string> */
    public $properties;
}
