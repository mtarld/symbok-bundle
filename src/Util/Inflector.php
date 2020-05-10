<?php

namespace Mtarld\SymbokBundle\Util;

class Inflector
{
    public static function singularize(string $plural): string
    {
        return is_array($singular = \Symfony\Component\Inflector\Inflector::singularize($plural)) ? end($singular) : $singular;
    }
}
