<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Nullable
{
    /** @var bool */
    public $nullable = true;
}
