<?php

namespace Mtarld\SymbokBundle\Util;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Mixed_;

class TypeFormatter
{
    public function asString(?Type $type, bool $nullable): ?string
    {
        if (null === $type || $type instanceof Mixed_) {
            return null;
        }

        if ($type instanceof Array_) {
            $type = 'array';
        }

        return true === $nullable ? '?'.$type : (string) $type;
    }

    public function nestedAsString(?Type $type): ?string
    {
        if (null === $type || $type instanceof Mixed_) {
            return null;
        }

        if ($type instanceof Array_) {
            $type = $type->getValueType();
        }

        return (string) $type;
    }
}
