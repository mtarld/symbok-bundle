<?php

namespace Mtarld\SymbokBundle\Util;

use LogicException;

/**
 * @internal
 * @final
 */
class MethodNameGenerator
{
    public const METHOD_GET = 'get';
    public const METHOD_IS = 'is';
    public const METHOD_HAS = 'has';
    public const METHOD_SET = 'set';
    public const METHOD_ADD = 'add';
    public const METHOD_REMOVE = 'remove';

    public static function generate(string $name, string $type): string
    {
        if (!in_array($type, [
            self::METHOD_GET,
            self::METHOD_IS,
            self::METHOD_HAS,
            self::METHOD_SET,
            self::METHOD_ADD,
            self::METHOD_REMOVE,
        ], true)) {
            throw new LogicException(sprintf('Unknown method type: "%s"', $type));
        }

        return $type.self::asCamelCase($name);
    }

    private static function asCamelCase(string $str): string
    {
        return strtr(ucwords(strtr($str, ['_' => ' ', '.' => ' ', '\\' => ' '])), [' ' => '']);
    }
}
