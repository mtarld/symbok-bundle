<?php

namespace Mtarld\SymbokBundle\Tests\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\GetterPass;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\GetterBuilder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group compiler
 */
class GetterPassTest extends TestCase
{
    /**
     * @dataProvider supportDataProvider
     * @testdox support return $result when method found is $hasMethod and require is $require
     */
    public function testSupport(bool $hasMethod, bool $require, bool $result): void
    {
        $behavior = $this->createMock(PropertyBehavior::class);
        $behavior
            ->method('requireGetter')
            ->willReturn($require)
        ;

        $finder = $this->createMock(PhpCodeFinder::class);
        $finder
            ->method('hasMethod')
            ->willReturn($hasMethod)
        ;

        $pass = new GetterPass(
            $behavior,
            $finder,
            $this->createMock(GetterBuilder::class)
        );

        $property = $this->createMock(SymbokProperty::class);

        $this->assertSame($result, $pass->support($property));
    }

    public function supportDataProvider(): iterable
    {
        yield [true, true, false];
        yield [false, true, true];
        yield [false, false, false];
        yield [true, false, false];
    }

    /**
     * @testdox Add method to class
     */
    public function testProcessClass(): void
    {
        $builder = $this->createMock(GetterBuilder::class);
        $builder
            ->method('build')
            ->willReturn(new ClassMethod('dummy'))
        ;

        $class = (new SymbokClass())->setStatements([]);

        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getClass')
            ->willReturn($class)
        ;

        $pass = new GetterPass(
            $this->createMock(PropertyBehavior::class),
            $this->createMock(PhpCodeFinder::class),
            $builder
        );

        $statements = $pass->process($property)->getStatements();

        $this->assertCount(1, $statements);
        $this->assertSame('dummy', $statements[0]->name->name);
    }
}
