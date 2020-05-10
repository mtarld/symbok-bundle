<?php

namespace Mtarld\SymbokBundle\Model\Relation;

/**
 * @internal
 */
final class OneToManyRelation extends DoctrineCollectionRelation
{
    public function isOwning(): bool
    {
        return false;
    }
}
