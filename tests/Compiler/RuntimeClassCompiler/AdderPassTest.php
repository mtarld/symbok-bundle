<?php

namespace Mtarld\SymbokBundle\Tests\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\AdderPass;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\AdderBuilder;
use Mtarld\SymbokBundle\Model\Relation\ManyToManyRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group compiler
 */
class AdderPassTest extends TestCase
{
    /**
     * @dataProvider supportDataProvider
     * @testdox support return $result when require is $require, method found is $hasMethod, type is array is $arrayType and collection relation is $relation
     */
    public function testSupport(bool $require, bool $hasMethod, bool $arrayType, bool $relation, bool $result): void
    {
        $behavior = $this->createMock(PropertyBehavior::class);
        $behavior
            ->method('requireAdder')
            ->willReturn($require)
        ;

        $finder = $this->createMock(PhpCodeFinder::class);
        $finder
            ->method('hasMethod')
            ->willReturn($hasMethod)
        ;

        $pass = new AdderPass(
            $behavior,
            $finder,
            $this->createMock(AdderBuilder::class)
        );

        $property = $this->createMock(SymbokProperty::class);
        if (true === $arrayType) {
            $property
                ->method('getType')
                ->willReturn((new Array_()))
            ;
        }
        if (true === $relation) {
            $property
                ->method('getRelation')
                ->willReturn(new ManyToManyRelation())
            ;
        }

        $this->assertSame($result, $pass->support($property));
    }

    public function supportDataProvider(): iterable
    {
        yield [true, false, true, false, true];
        yield [true, false, false, true, true];
        yield [true, false, false, false, false];
        yield [true, true, false, true, false];
        yield [true, false, false, false, false];
    }

    /**
     * @testdox Add method to class
     */
    public function testProcessClass(): void
    {
        $builder = $this->createMock(AdderBuilder::class);
        $builder
            ->method('build')
            ->willReturn(new ClassMethod('dummy'))
        ;

        $class = new SymbokClass('foo', [], new DocBlock('bar'), [], [], new Context('baz'));

        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getClass')
            ->willReturn($class)
        ;

        $pass = new AdderPass(
            $this->createMock(PropertyBehavior::class),
            $this->createMock(PhpCodeFinder::class),
            $builder
        );

        $statements = $pass->process($property)->getStatements();

        $this->assertCount(1, $statements);

        /** @var ClassMethod $method */
        $method = $statements[0];
        $this->assertSame('dummy', (string) $method->name);
    }
}
