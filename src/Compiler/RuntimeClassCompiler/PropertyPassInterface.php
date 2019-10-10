<?php

namespace Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;

interface PropertyPassInterface
{
    public function process(SymbokProperty $property): SymbokClass;

    public function support(SymbokProperty $property): bool;
}
