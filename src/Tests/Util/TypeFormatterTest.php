<?php

namespace Mtarld\SymbokBundle\Tests\Util;

use Mtarld\SymbokBundle\Util\TypeFormatter;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group util
 */
class TypeFormatterTest extends TestCase
{
    /**
     * @dataProvider formatAsStringDataProvider
     * @testdox Type is well formatted for $type type and $nullable nullable
     */
    public function testFormatAsString(?Type $type, bool $nullable, ?string $result): void
    {
        $this->assertSame($result, (new TypeFormatter())->asString($type, $nullable));
    }

    public function formatAsStringDataProvider(): iterable
    {
        yield [null, false, null];
        yield [new Mixed_(), false, null];
        yield [new Integer(), false, 'int'];
        yield [new Integer(), true, '?int'];

        $arrayType = new Array_(new Integer());
        yield [$arrayType, true, '?array'];
    }

    /**
     * @dataProvider formatNestedAsStringDataProvider
     * @testdox Type is well formatted for $type type
     */
    public function testFormatNestedAsString(?Type $type, ?string $result): void
    {
        $this->assertSame($result, (new TypeFormatter())->nestedAsString($type));
    }

    public function formatNestedAsStringDataProvider(): iterable
    {
        yield [null, null];
        yield [new Mixed_(), null];
        yield [new Integer(), 'int'];

        $arrayType = new Array_(new Integer());
        yield [$arrayType, 'int'];
    }
}
