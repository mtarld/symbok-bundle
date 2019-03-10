<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class ToString
{
    /** @var array */
    public $properties;
}
