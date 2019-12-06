<?php

namespace Mtarld\SymbokBundle\Model\Relation;

final class OneToManyRelation extends DoctrineCollectionRelation
{
    public function isOwning(): bool
    {
        return false;
    }
}
