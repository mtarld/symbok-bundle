<?php

namespace Mtarld\SymbokBundle\Context;

use Mtarld\SymbokBundle\Exception\SymbokException;
use phpDocumentor\Reflection\Types\Context;

class ContextHolder
{
    /** @var Context */
    private $context = null;

    public function buildContext($namespace, array $namespaceAliases = []): void
    {
        // Creates aliases for all namespaces (useful for phpdocumentor reflection docblock)
        foreach ($namespaceAliases as $alias => $namespaceAlias) {
            if (is_int($alias)) {
                $parts = explode('\\', $namespaceAlias);
                $newAlias = $parts[sizeof($parts) - 1];
                $namespaceAliases[$newAlias] = $namespaceAlias;
                unset($namespaceAliases[$alias]);
            }
        }

        $this->context = new Context($namespace, $namespaceAliases);
    }

    public function getContext(): Context
    {
        if (!$this->context) {
            throw new SymbokException('Type context was not built');
        }

        return $this->context;
    }
}
