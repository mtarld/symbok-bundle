<?php

namespace Mtarld\SymbokBundle\Replacer;

/**
 * @internal
 */
interface ReplacerInterface
{
    public function replace(string $classFqcn): string;
}
