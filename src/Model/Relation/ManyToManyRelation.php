<?php

namespace Mtarld\SymbokBundle\Model\Relation;

class ManyToManyRelation extends DoctrineCollectionRelation
{
    public function setIsOwning(bool $isOwning)
    {
        $this->isOwning = $isOwning;

        return $this;
    }
}
