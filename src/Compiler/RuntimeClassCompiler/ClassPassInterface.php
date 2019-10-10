<?php

namespace Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Model\SymbokClass;

interface ClassPassInterface
{
    public function process(SymbokClass $class): SymbokClass;

    public function support(SymbokClass $class): bool;
}
