<?php

namespace Mtarld\SymbokBundle\Model\Relation;

/**
 * @internal
 */
final class ManyToOneRelation extends DoctrineSingleRelation
{
    public function isOwning(): bool
    {
        return true;
    }
}
