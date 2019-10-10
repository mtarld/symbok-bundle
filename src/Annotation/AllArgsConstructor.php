<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class AllArgsConstructor implements AnnotationInterface
{
    /** @var bool */
    public $nullable;
}
