<?php

namespace Mtarld\SymbokBundle\Tests\Finder;

use Mtarld\SymbokBundle\Exception\CodeFindingException;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @group unit
 * @group finder
 */
class PhpCodeFinderTest extends TestCase
{
    public function testFindNamespace(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $stmts = [
            new Namespace_(),
        ];

        $this->assertInstanceOf(Namespace_::class, $finder->findNamespace($stmts));
    }

    public function testFindNamespaceExceptionWhenDouble(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $stmts = [
            new Namespace_(),
            new Namespace_(),
        ];

        $this->expectException(CodeFindingException::class);
        $this->expectExceptionMessage('More than one');

        $finder->findNamespace($stmts);
    }

    public function testFindNamespaceExceptionWhenZero(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $stmts = [
            new Class_('Foo'),
        ];

        $this->expectException(CodeFindingException::class);
        $this->expectExceptionMessage('No');

        $finder->findNamespace($stmts);
    }

    public function testFindClass(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $stmts = [
            new Namespace_(new Name('Foo'), [
                new Class_('Foo'),
            ]),
        ];

        $this->assertInstanceOf(Class_::class, $finder->findClass($stmts));
    }

    public function testFindClassExceptionWhenDouble(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $stmts = [
            new Namespace_(new Name('Foo'), [
                new Class_('Foo'),
                new Class_('Bar'),
            ]),
        ];

        $this->expectException(CodeFindingException::class);
        $this->expectExceptionMessage('More than one');

        $finder->findClass($stmts);
    }

    public function testFindClassExceptionWhenZero(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $stmts = [
            new Namespace_(new Name('Foo'), [
            ]),
        ];

        $this->expectException(CodeFindingException::class);
        $this->expectExceptionMessage('No');

        $finder->findClass($stmts);
    }

    public function testFindAliases(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $expectedUse = new Use_([new UseUse(new Name('Bar'), 'alias')]);
        $expectedUse2 = new Use_([new UseUse(new Name('Baz'))]);
        $stmts = [
            new Namespace_(new Name('Foo'), [
                $expectedUse,
                $expectedUse2,
            ]),
        ];

        $uses = $finder->findAliases($stmts);

        $this->assertCount(2, $uses);
        $this->assertArrayHasKey('alias', $uses);
        $this->assertSame('Bar', $uses['alias']);

        $expectedGroupUse = new GroupUse(new Name('prefix'), [
            new UseUse(new Name('Bar')),
            new UseUse(new Name('Baz')),
        ]);
        $stmts = [
            new Namespace_(new Name('Foo'), [
                $expectedGroupUse,
            ]),
        ];

        $uses = $finder->findAliases($stmts);

        $this->assertCount(2, $uses);
        $this->assertArrayHasKey('Bar', $uses);
        $this->assertArrayHasKey('Baz', $uses);
        $this->assertSame('prefix\Bar', $uses['Bar']);
        $this->assertSame('prefix\Baz', $uses['Baz']);
    }

    public function testFindProperties(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $class = new Class_('Bar');
        $class->stmts = [new Property(Class_::MODIFIER_PUBLIC, [new PropertyProperty('baz')])];
        $stmts = [
            new Namespace_(new Name('Foo'), [
                $class,
            ]),
        ];

        $properties = $finder->findProperties($stmts);
        $this->assertCount(1, $properties);
    }

    public function testFindMethods(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $class = new Class_('Bar');
        $class->stmts = [new ClassMethod('baz')];
        $stmts = [
            new Namespace_(new Name('Foo'), [
                $class,
            ]),
        ];

        $methods = $finder->findMethods($stmts);
        $this->assertCount(1, $methods);
    }

    public function testFindMethod(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $class = new Class_('Bar');
        $class->stmts = [new ClassMethod('baz')];
        $stmts = [
            new Namespace_(new Name('Foo'), [
                $class,
            ]),
        ];

        $method = $finder->findMethod('bar', $stmts);
        $this->assertNull($method);

        $method = $finder->findMethod('baz', $stmts);
        $this->assertInstanceOf(ClassMethod::class, $method);

        $stmts = [
            new Namespace_(new Name('Foo'), [
                new Class_('Bar'),
            ]),
        ];
        $method = $finder->findMethod('foo', $stmts);
        $this->assertNull($method);
    }

    public function testHasMethod(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());
        $class = new Class_('Bar');
        $class->stmts = [new ClassMethod('baz')];
        $stmts = [
            new Namespace_(new Name('Foo'), [
                $class,
            ]),
        ];

        $method = $finder->hasMethod('bar', $stmts);
        $this->assertFalse($method);
    }

    public function testFindPropertyType(): void
    {
        $finder = new PhpCodeFinder(new NullLogger());

        $property = new Property(
            Class_::MODIFIER_PRIVATE,
            [new PropertyProperty('foo')],
            [],
            new NullableType(new Identifier('string'))
        );

        $this->assertEquals(new Identifier('string'), $finder->findPropertyType($property));

        $property = new Property(
            Class_::MODIFIER_PRIVATE,
            [new PropertyProperty('foo')],
            [],
            new NullableType(new Name(['App', 'Foo']))
        );

        $this->assertEquals(new Name(['App', 'Foo']), $finder->findPropertyType($property));

        $property = new Property(
            Class_::MODIFIER_PRIVATE,
            [new PropertyProperty('foo')]
        );

        $this->assertNull($finder->findPropertyType($property));
    }
}
