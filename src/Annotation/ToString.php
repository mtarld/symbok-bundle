<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class ToString implements AnnotationInterface
{
    /** @var array */
    public $properties;
}
