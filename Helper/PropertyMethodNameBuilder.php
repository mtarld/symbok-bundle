<?php

namespace Mtarld\SymbokBundle\Helper;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Boolean;

abstract class PropertyMethodNameBuilder
{
    public static function buildGetterMethodName(string $name, Type $type): string
    {
        $methodName = 'get' . ucfirst($name);
        if (is_a($type, Boolean::class)) {
            $methodName = 'is' . ucfirst($name);
        }

        return $methodName;
    }

    public static function buildSetterMethodName(string $name): string
    {
        return 'set' . ucfirst($name);
    }

    public static function buildAdderMethodName(string $name): string
    {
        $name = Singularize::getSingular($name);

        return 'add' . ucfirst($name);
    }

    public static function buildRemoverMethodName(string $name): string
    {
        $name = Singularize::getSingular($name);

        return 'remove' . ucfirst($name);
    }
}
