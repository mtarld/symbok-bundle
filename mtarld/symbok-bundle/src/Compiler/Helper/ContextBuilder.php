<?php

namespace Mtarld\SymbokBundle\Compiler\Helper;

use phpDocumentor\Reflection\Types\Context;

abstract class ContextBuilder
{
    // Creates aliases for all namespaces (useful for phpdocumentor reflection docblock)
    public static function build($namespace, array $namespaceAliases = []): Context
    {
        foreach ($namespaceAliases as $alias => $namespaceAlias) {
            if (is_int($alias)) {
                $parts = explode('\\', $namespaceAlias);
                $newAlias = $parts[sizeof($parts) - 1];
                $namespaceAliases[$newAlias] = $namespaceAlias;
                unset($namespaceAliases[$alias]);
            }
        }

        return new Context($namespace, $namespaceAliases);
    }
}
