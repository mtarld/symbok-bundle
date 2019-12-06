<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Nullable implements AnnotationInterface
{
    /** @var bool */
    public $nullable = true;
}
