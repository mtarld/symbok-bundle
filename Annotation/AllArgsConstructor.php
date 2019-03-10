<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class AllArgsConstructor
{
    /** @var bool */
    public $nullable;
}
