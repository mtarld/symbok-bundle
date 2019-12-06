<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Setter implements AnnotationInterface
{
    /** @var bool */
    public $nullable;

    /** @var bool */
    public $fluent;

    /** @var bool */
    public $updateOtherSide;

    /** @var bool */
    public $add = true;

    /** @var bool */
    public $remove = true;
}
