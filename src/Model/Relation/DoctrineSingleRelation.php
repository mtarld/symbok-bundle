<?php

namespace Mtarld\SymbokBundle\Model\Relation;

use Mtarld\SymbokBundle\Util\MethodNameGenerator;

/**
 * @internal
 */
abstract class DoctrineSingleRelation extends DoctrineRelation
{
    public function getTargetSetterMethodName(): string
    {
        return MethodNameGenerator::generate($this->getMethodTargetName(), MethodNameGenerator::METHOD_SET);
    }
}
