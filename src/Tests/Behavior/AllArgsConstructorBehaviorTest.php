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
            ->willReturnCallback($callable)
        ;

        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getAnnotation')
            ->willReturnCallback($callable)
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
            ['nullable' => false]
        );

        $this->assertSame($result, $allArgsConstructorBehavior->isNullable($property));
    }

    public function isNullableDataProvider(): iterable
    {
        yield [
            static function (string $class): void {},
            null,
            false,
            'Default is ok',
        ];

        yield [
            /**
             * @param class-string $class
             *
             * @return mixed
             */
            static function (string $class) {
                return new $class();
            },
            null,
            false,
            'AllArgsConstructor without nullable is falling back to default',
        ];

        yield [
            static function (string $class): ?AllArgsConstructor {
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
            /**
             * @return AllArgsConstructor|Data|null
             */
            static function (string $class) {
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
            static function (string $class): ?Data {
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
            static function (string $class): void {},
            true,
            true,
            'Nullable property is ok',
        ];

        yield [
            static function (string $class): ?Data {
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
