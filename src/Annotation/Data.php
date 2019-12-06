<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Data implements AnnotationInterface
{
    /** @var bool */
    public $nullable;

    /** @var bool */
    public $constructorNullable;

    /** @var bool */
    public $updateOtherSide;

    /** @var bool */
    public $fluent;

    /** @var bool */
    public $add = true;

    /** @var bool */
    public $remove = true;
}
