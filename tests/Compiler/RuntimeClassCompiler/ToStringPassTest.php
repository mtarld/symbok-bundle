<?php

namespace Mtarld\SymbokBundle\Tests\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\ClassBehavior;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\ToStringPass;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\ToStringBuilder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group compiler
 */
class ToStringPassTest extends TestCase
{
    /**
     * @dataProvider supportDataProvider
     * @testdox support return $result when require is $require and method found is $hasMethod
     */
    public function testSupport(bool $require, bool $hasMethod, bool $result): void
    {
        $behavior = $this->createMock(ClassBehavior::class);
        $behavior
            ->method('requireToString')
            ->willReturn($require)
        ;

        $finder = $this->createMock(PhpCodeFinder::class);
        $finder
            ->method('hasMethod')
            ->willReturn($hasMethod)
        ;

        $pass = new ToStringPass(
            $behavior,
            $finder,
            $this->createMock(ToStringBuilder::class)
        );
        $class = $this->createMock(SymbokClass::class);

        $this->assertSame($result, $pass->support($class));
    }

    public function supportDataProvider(): iterable
    {
        yield [true, false, true];
        yield [false, false, false];
        yield [true, true, false];
    }

    /**
     * @testdox Add method to class
     */
    public function testProcessClass(): void
    {
        $builder = $this->createMock(ToStringBuilder::class);
        $builder
            ->method('build')
            ->willReturn(new ClassMethod('dummy'))
        ;

        $class = new SymbokClass('foo', [], new DocBlock('bar'), [], [], new Context('baz'));

        $pass = new ToStringPass(
            $this->createMock(ClassBehavior::class),
            $this->createMock(PhpCodeFinder::class),
            $builder
        );

        $statements = $pass->process($class)->getStatements();

        $this->assertCount(1, $statements);

        /** @var ClassMethod $method */
        $method = $statements[0];
        $this->assertSame('dummy', (string) $method->name);
    }
}
