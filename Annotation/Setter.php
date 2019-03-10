<?php

namespace Mtarld\SymbokBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Setter
{
    /** @var bool */
    public $nullable;

    /** @var bool */
    public $fluent;

    /** @var bool */
    public $noAdd = false;

    /** @var bool */
    public $noRemove = false;
}
