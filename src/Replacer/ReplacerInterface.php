<?php

namespace Mtarld\SymbokBundle\Replacer;

interface ReplacerInterface
{
    public function replace(string $class): string;
}
