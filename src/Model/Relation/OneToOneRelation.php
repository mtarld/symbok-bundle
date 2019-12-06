<?php

namespace Mtarld\SymbokBundle\Model\Relation;

final class OneToOneRelation extends DoctrineSingleRelation
{
    public function setIsOwning(bool $isOwning)
    {
        $this->isOwning = $isOwning;

        return $this;
    }
}
