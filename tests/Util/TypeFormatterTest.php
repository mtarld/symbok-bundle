<?php

namespace Mtarld\SymbokBundle\Tests\Util;

use Mtarld\SymbokBundle\Util\TypeFormatter;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Callable_;
use phpDocumentor\Reflection\Types\ClassString;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Parent_;
use phpDocumentor\Reflection\Types\Resource_;
use phpDocumentor\Reflection\Types\Scalar;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\This;
use phpDocumentor\Reflection\Types\Void_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\MagicConst\Line;
use PhpParser\Node\UnionType;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group util
 */
class TypeFormatterTest extends TestCase
{
    /** @var TypeFormatter */
    private $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatter = new TypeFormatter();
    }

    /**
     * @param mixed $type
     *
     * @dataProvider asStringDataProvider
     * @testdox Type $type is converted to $result
     */
    public function testAsString($type, ?string $result): void
    {
        $this->assertSame($result, $this->formatter->asPhpString($type));
    }

    public function asStringDataProvider(): iterable
    {
        yield [null, null];
        yield [new Array_(new Integer(), new String_()), 'array'];
        yield [new Boolean(), 'bool'];
        yield [new Callable_(), 'callable'];
        yield [new Compound([new Mixed_(), new Integer()]), null];
        yield [new Float_(), 'float'];
        yield [new Integer(), 'int'];
        yield [new Iterable_(), 'iterable'];
        yield [new Mixed_(), null];
        yield [new Null_(), null];
        yield [new Nullable(new Integer()), '?int'];
        yield [new Nullable(new Array_(new Integer())), '?array'];
        yield [new Object_(new Fqsen('\App\Foo')), '\App\Foo'];
        yield [new Object_(), 'object'];
        yield [new Parent_(), 'parent'];
        yield [new Resource_(), null];
        yield [new Scalar(), null];
        yield [new Self_(), 'self'];
        yield [new Static_(), null];
        yield [new String_(), 'string'];
        yield [new This(), null];
        yield [new Void_(), 'void'];
        yield [new Identifier('foo'), 'foo'];
        yield [new Name(['App', 'Foo']), 'App\Foo'];
        yield [new NullableType((string) new String_()), '?string'];
        yield [new UnionType([new Identifier((string) new Integer()), new Identifier((string) new Nullable(new String_()))]), null];
        yield [new Line(), null];
        yield ['foo', null];

        if (class_exists(ClassString::class)) {
            yield [new ClassString(new Fqsen('\App\Foo')), null];
        }
        if (class_exists(Collection::class)) {
            yield [new Collection(new Fqsen('\App\Collection'), new Integer(), new Integer()), '\App\Collection'];
        }
        yield ((new \ReflectionClass(Iterable_::class))->hasMethod('__construct'))
            ? [new Iterable_(new Integer(), new String_()), 'iterable']
            : [new Iterable_(), 'iterable']
        ;
    }

    /**
     * @param mixed $type
     *
     * @dataProvider asDocumentationStringDataProvider
     * @testdox Documentation type $type is converted to $result
     */
    public function testAsDocumentationString($type, ?string $result): void
    {
        $this->assertSame($result, $this->formatter->asDocumentationString($type));
    }

    public function asDocumentationStringDataProvider(): iterable
    {
        yield [null, ''];
        yield [new Boolean(), 'bool'];
        yield [new Callable_(), 'callable'];
        yield [new Compound([new Mixed_(), new Integer()]), 'mixed|int'];
        yield [new Float_(), 'float'];
        yield [new Integer(), 'int'];
        yield [new Mixed_(), 'mixed'];
        yield [new Null_(), 'null'];
        yield [new Nullable(new Integer()), 'int|null'];
        yield [new Nullable(new Array_(new Integer())), 'int[]|null'];
        yield [new Object_(new Fqsen('\App\Foo')), '\App\Foo'];
        yield [new Object_(), 'object'];
        yield [new Parent_(), 'parent'];
        yield [new Resource_(), 'resource'];
        yield [new Scalar(), 'scalar'];
        yield [new Self_(), 'self'];
        yield [new Static_(), 'static'];
        yield [new String_(), 'string'];
        yield [new This(), '$this'];
        yield [new Void_(), 'void'];
        yield [new Identifier('foo'), 'foo'];
        yield [new Name(['App', 'Foo']), 'App\Foo'];
        yield [new NullableType((string) new String_()), 'string|null'];
        yield ['foo', ''];
        yield [new Line(), ''];

        if (class_exists(ClassString::class)) {
            yield [new ClassString(new Fqsen('\App\Foo')), 'class-string<\App\Foo>'];
        }
        if (class_exists(Collection::class)) {
            yield [new Collection(new Fqsen('\App\Collection'), new Integer(), new Integer()), '\App\Collection<int,int>'];
        }

        yield ((new \ReflectionClass(Iterable_::class))->hasMethod('__construct'))
            ? [new Iterable_(new Integer(), new String_()), 'iterable<string,int>']
            : [new Iterable_(), 'iterable']
        ;
        yield ((new \ReflectionClass(Array_::class))->getParentClass() instanceof \ReflectionClass)
            ? [new Array_(new Integer(), new String_()), 'array<string,int>']
            : [new Array_(new Integer(), new String_()), 'int[]']
        ;
    }

    /**
     * @param mixed $type
     *
     * @dataProvider nestedAsStringDataProvider
     * @testdox Nested type for $type is $result
     */
    public function testNestedAsString(?Type $type, ?string $result): void
    {
        $this->assertSame($result, $this->formatter->nestedAsPhpString($type));
    }

    public function nestedAsStringDataProvider(): iterable
    {
        yield [null, null];
        yield [new Mixed_(), null];
        yield [new Integer(), 'int'];
        yield [new Array_(new Integer()), 'int'];
        yield [new Array_(new Nullable(new String_())), '?string'];
        yield [new Array_(new Array_(new Nullable(new String_()))), 'array'];
    }

    /**
     * @param mixed $type
     *
     * @dataProvider typeAsDocumentationTypeDataProvider
     * @testdox Documentation type $type is converted to $result type
     */
    public function testAsDocumentationType($type, Type $result): void
    {
        $this->assertEquals($result, $this->formatter->asDocumentationType($type));
    }

    public function typeAsDocumentationTypeDataProvider(): iterable
    {
        yield [null, new Mixed_()];
        yield [new Mixed_(), new Mixed_()];
        yield [new UnionType([new Identifier((string) new Integer()), new Identifier((string) new Nullable(new String_()))]), new Compound([new Integer(), new Nullable(new String_())])];
        yield [new Nullable(new Integer()), new Compound([new Integer(), new Null_()])];
        yield [new Nullable(new Array_(new Integer())), new Compound([new Array_(new Integer()), new Null_()])];
    }
}
