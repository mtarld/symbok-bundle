<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Data
{
    /** @var bool */
    public $nullable;

    /** @var bool */
    public $constructorNullable;

    /** @var bool */
    public $fluentSetters;
}
