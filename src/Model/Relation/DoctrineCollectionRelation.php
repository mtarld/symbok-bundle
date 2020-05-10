<?php

namespace Mtarld\SymbokBundle\Model\Relation;

use Mtarld\SymbokBundle\Util\MethodNameGenerator;

/**
 * @internal
 */
abstract class DoctrineCollectionRelation extends DoctrineRelation
{
    public function getTargetSetterMethodName(): string
    {
        return MethodNameGenerator::generate($this->getMethodTargetName(), MethodNameGenerator::METHOD_ADD);
    }

    public function getTargetRemoverMethodName(): string
    {
        return MethodNameGenerator::generate($this->getMethodTargetName(), MethodNameGenerator::METHOD_REMOVE);
    }
}
