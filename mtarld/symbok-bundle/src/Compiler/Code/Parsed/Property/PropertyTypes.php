<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parsed\Property;

use phpDocumentor\Reflection\Type;

class PropertyTypes
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

