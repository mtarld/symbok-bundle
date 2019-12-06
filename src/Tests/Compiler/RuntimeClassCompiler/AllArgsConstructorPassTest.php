<?php

namespace Mtarld\SymbokBundle\Tests\Compiler\Pass;

use Mtarld\SymbokBundle\Behavior\ClassBehavior;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\AllArgsConstructorPass;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\AllArgsConstructorBuilder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group compiler
 */
class AllArgsConstructorPassTest extends TestCase
{
    /**
     * @dataProvider supportDataProvider
     * @testdox support return $result when require is $require and method found is $hasMethod
     */
    public function testSupport(bool $require, bool $hasMethod, bool $result): void
    {
        $behavior = $this->createMock(ClassBehavior::class);
        $behavior
            ->method('requireAllArgsConstructor')
            ->willReturn($require)
        ;

        $finder = $this->createMock(PhpCodeFinder::class);
        $finder
            ->method('hasMethod')
            ->willReturn($hasMethod)
        ;

        $pass = new AllArgsConstructorPass(
            $behavior,
            $finder,
            $this->createMock(AllArgsConstructorBuilder::class)
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
        $builder = $this->createMock(AllArgsConstructorBuilder::class);
        $builder
            ->method('build')
            ->willReturn(new ClassMethod('dummy'))
        ;

        $class = (new SymbokClass())->setStatements([]);

        $pass = new AllArgsConstructorPass(
            $this->createMock(ClassBehavior::class),
            $this->createMock(PhpCodeFinder::class),
            $builder
        );

        $statements = $pass->process($class)->getStatements();

        $this->assertCount(1, $statements);
        $this->assertSame('dummy', $statements[0]->name->name);
    }
}
