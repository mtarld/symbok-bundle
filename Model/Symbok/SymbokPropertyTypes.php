<?php

namespace Mtarld\SymbokBundle\Model\Symbok;

use phpDocumentor\Reflection\Type;

class SymbokPropertyTypes
{
    /** @var Type */
    private $baseType;

    /** @var Type */
    private $relationType;

    public function __construct(
        Type $baseType,
        ?Type $relationType
    ) {
        $this->baseType = $baseType;
        $this->relationType = $relationType;
    }

    public function getBaseType(): Type
    {
        return $this->baseType;
    }

    public function getRelationType(): ?Type
    {
        return $this->relationType;
    }
}
