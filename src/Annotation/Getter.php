<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Getter implements AnnotationInterface
{
    /** @var bool */
    public $nullable;

    /** @var bool */
    public $hasPrefix = false;
}
