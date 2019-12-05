<?php

namespace Mtarld\SymbokBundle\Tests\Behavior;

use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Behavior\AllArgsConstructorBehavior;
use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group behavior
 */
class AllArgsConstructorBehaviorTest extends TestCase
{
    /**
     * @dataProvider isNullableDataProvider
     * @testdox $testDox
     */
    public function testIsNullable(callable $callable, ?bool $propertyNullable, bool $result, string $testDox): void
    {
        $class = $this->createMock(SymbokClass::class);
        $class
            ->method('getAnnotation')
            ->will($this->returnCallback($callable))
        ;

        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getAnnotation')
            ->will($this->returnCallback($callable))
        ;
        $property
            ->method('getClass')
            ->willReturn($class)
        ;

        $propertyBehavior = $this->createMock(PropertyBehavior::class);
        $propertyBehavior
            ->method('isNullable')
            ->willReturn($propertyNullable)
        ;
        $allArgsConstructorBehavior = new AllArgsConstructorBehavior(
            $propertyBehavior,
            [
                'defaults' => [
                    'constructor' => ['nullable' => false],
                ],
            ]
        );

        $this->assertSame($result, $allArgsConstructorBehavior->isNullable($property));
    }

    public function isNullableDataProvider(): iterable
    {
        yield [
            function ($class) {
                return null;
            },
            null,
            false,
            'Default is ok',
        ];

        yield [
            function ($class) {
                return new $class();
            },
            null,
            false,
            'AllArgsConstructor without nullable is falling back to default',
        ];

        yield [
            function ($class) {
                if (AllArgsConstructor::class === $class) {
                    $annotation = new AllArgsConstructor();
                    $annotation->nullable = true;

                    return $annotation;
                }

                return null;
            },
            null,
            true,
            'AllArgsConstructor with nullable is ok',
        ];

        yield [
            function ($class) {
                if (AllArgsConstructor::class === $class) {
                    $annotation = new AllArgsConstructor();
                    $annotation->nullable = false;

                    return $annotation;
                }

                if (Data::class === $class) {
                    $annotation = new Data();
                    $annotation->constructorNullable = true;

                    return $annotation;
                }

                return null;
            },
            null,
            false,
            'AllArgConstructor is prior to data',
        ];

        yield [
            function ($class) {
                if (Data::class === $class) {
                    $annotation = new Data();
                    $annotation->constructorNullable = true;

                    return $annotation;
                }

                return null;
            },
            null,
            true,
            'Data with nullable is ok',
        ];

        yield [
            function ($class) {
                return null;
            },
            true,
            true,
            'Nullable property is ok',
        ];

        yield [
            function ($class) {
                if (Data::class === $class) {
                    $annotation = new Data();
                    $annotation->constructorNullable = true;

                    return $annotation;
                }

                return null;
            },
            false,
            true,
            'Data is prior to property',
        ];
    }
}
