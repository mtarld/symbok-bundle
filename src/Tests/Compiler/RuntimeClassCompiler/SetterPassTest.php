<?php

namespace Mtarld\SymbokBundle\Tests\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\SetterPass;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\SetterBuilder;
use Mtarld\SymbokBundle\Model\Relation\ManyToManyRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group compiler
 */
class SetterPassTest extends TestCase
{
    /**
     * @dataProvider supportDataProvider
     * @testdox support return $result when method found is $hasMethod and require is $require and relation is $relation
     */
    public function testSupport(bool $hasMethod, bool $require, ?string $relation, bool $result): void
    {
        $behavior = $this->createMock(PropertyBehavior::class);
        $behavior
            ->method('requireSetter')
            ->willReturn($require)
        ;

        $finder = $this->createMock(PhpCodeFinder::class);
        $finder
            ->method('hasMethod')
            ->willReturn($hasMethod)
        ;

        $pass = new SetterPass(
            $behavior,
            $finder,
            $this->createMock(SetterBuilder::class)
        );

        $property = $this->createMock(SymbokProperty::class);
        if (!empty($relation)) {
            $property
                ->method('getRelation')
                ->willReturn(new $relation())
            ;
        }

        $this->assertSame($result, $pass->support($property));
    }

    public function supportDataProvider(): iterable
    {
        yield [true, true, null, false];
        yield [false, true, null, true];
        yield [false, true, ManyToManyRelation::class, false];
        yield [false, false, null, false];
    }

    /**
     * @testdox Add method to class
     */
    public function testProcessClass(): void
    {
        $builder = $this->createMock(SetterBuilder::class);
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

        $pass = new SetterPass(
            $this->createMock(PropertyBehavior::class),
            $this->createMock(PhpCodeFinder::class),
            $builder
        );

        $statements = $pass->process($property)->getStatements();

        $this->assertCount(1, $statements);
        $this->assertSame('dummy', $statements[0]->name->name);
    }
}
