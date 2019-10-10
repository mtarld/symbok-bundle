<?php

namespace Mtarld\SymbokBundle\Model\Relation;

class ManyToOneRelation extends DoctrineSingleRelation
{
    public function isOwning(): bool
    {
        return true;
    }
}
