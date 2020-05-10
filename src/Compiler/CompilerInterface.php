<?php

namespace Mtarld\SymbokBundle\Compiler;

use Mtarld\SymbokBundle\Model\SymbokClass;
use PhpParser\Node;

/**
 * @internal
 */
interface CompilerInterface
{
    /**
     * @param array<Node> $statements
     */
    public function compile(array $statements): SymbokClass;
}
