<?php

namespace Mtarld\SymbokBundle\Tests\Compiler\RuntimeClassCompiler;

use Mtarld\SymbokBundle\Behavior\ClassBehavior;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\ConstructorPass;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\MethodBuilder\ConstructorBuilder;
use Mtarld\SymbokBundle\Model\Relation\ManyToManyRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group compiler
 */
class ConstructorPassTest extends TestCase
{
    /**
     * @dataProvider supportDataProvider
     * @testdox support return $result when method found is $hasMethod and require allArgsConstructor is $require and has doctrine relation is $doctrineCollection
     */
    public function testSupport(bool $hasMethod, bool $require, bool $doctrineCollection, bool $result): void
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

        $pass = new ConstructorPass(
            $behavior,
            $finder,
            $this->createMock(ConstructorBuilder::class)
        );

        $class = $this->createMock(SymbokClass::class);
        if (true === $doctrineCollection) {
            $property = $this->createMock(SymbokProperty::class);
            $property
                ->method('getRelation')
                ->willReturn(new ManyToManyRelation())
            ;

            $class
                ->method('getProperties')
                ->willReturn([
                    $property,
                ])
            ;
        }

        $this->assertSame($result, $pass->support($class));
    }

    public function supportDataProvider(): iterable
    {
        yield [true, false, true, false];
        yield [false, false, true, true];
        yield [false, false, false, false];
        yield [false, true, true, false];
    }

    /**
     * @testdox Add method to class
     */
    public function testProcessClass(): void
    {
        $builder = $this->createMock(ConstructorBuilder::class);
        $builder
            ->method('build')
            ->willReturn(new ClassMethod('dummy'))
        ;

        $class = (new SymbokClass())->setStatements([]);

        $pass = new ConstructorPass(
            $this->createMock(ClassBehavior::class),
            $this->createMock(PhpCodeFinder::class),
            $builder
        );

        $statements = $pass->process($class)->getStatements();

        $this->assertCount(1, $statements);
        $this->assertSame('dummy', $statements[0]->name->name);
    }
}
