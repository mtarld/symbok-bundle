<?php

namespace Mtarld\SymbokBundle\Compiler;

use Mtarld\SymbokBundle\Model\SymbokClass;

interface CompilerInterface
{
    public function compile(array $statements): SymbokClass;
}
