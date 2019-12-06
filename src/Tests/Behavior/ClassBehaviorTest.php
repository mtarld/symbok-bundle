<?php

namespace Mtarld\SymbokBundle\Tests\Behavior;

use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\Behavior\ClassBehavior;
use Mtarld\SymbokBundle\Model\SymbokClass;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group behavior
 */
class ClassBehaviorTest extends TestCase
{
    /**
     * @dataProvider requireAllArgsConstructorDataProvider
     * @testdox requireAllArgsConstructor is $result when annotation is $annotation
     */
    public function testRequireAllArgsConstructor(?string $annotation, bool $result): void
    {
        $class = $this->createMock(SymbokClass::class);
        if (!empty($annotation)) {
            $class
                ->method('getAnnotation')
                ->willReturn(new $annotation())
            ;
        }

        $classBehavior = new ClassBehavior();

        $this->assertSame($result, $classBehavior->requireAllArgsConstructor($class));
    }

    public function requireAllArgsConstructorDataProvider(): iterable
    {
        yield [null, false];
        yield [ToString::class, false];
        yield [AllArgsConstructor::class, true];
    }

    /**
     * @dataProvider requireToStringDataProvider
     * @testdox requireToString is $result when annotation is $annotation
     */
    public function testRequireToString(?string $annotation, bool $result): void
    {
        $class = $this->createMock(SymbokClass::class);
        if (!empty($annotation)) {
            $class
                ->method('getAnnotation')
                ->willReturn(new $annotation())
            ;
        }

        $classBehavior = new ClassBehavior();

        $this->assertSame($result, $classBehavior->requireToString($class));
    }

    public function requireToStringDataProvider(): iterable
    {
        yield [null, false];
        yield [AllArgsConstructor::class, false];
        yield [ToString::class, true];
    }
}
