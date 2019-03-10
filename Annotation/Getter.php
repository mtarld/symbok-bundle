<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Getter
{
    /** @var bool */
    public $nullable;
}
