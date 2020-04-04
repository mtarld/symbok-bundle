<?php

namespace Mtarld\SymbokBundle\Tests\Behavior;

use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Behavior\SetterBehavior;
use Mtarld\SymbokBundle\Model\Relation\ManyToOneRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group behavior
 */
class SetterBehaviorTest extends TestCase
{
    /**
     * @dataProvider isFluentDataProvider
     * @testdox isFluent is $result when $testDox
     */
    public function testIsFluent(callable $callable, bool $result, string $testDox): void
    {
        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getAnnotation')
            ->willReturnCallback($callable)
        ;

        $propertyBehavior = $this->createMock(PropertyBehavior::class);
        $setterBehavior = new SetterBehavior(
            $propertyBehavior,
            ['fluent' => false]
        );

        $this->assertSame($result, $setterBehavior->isFluent($property));
    }

    public function isFluentDataProvider(): iterable
    {
        yield [
            static function (string $class): void {},
            false,
            'no annotation',
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
            false,
            'setter annotation',
        ];

        yield [
            static function (string $class): ?Data {
                if (Data::class === $class) {
                    $data = new Data();
                    $data->fluent = true;

                    return $data;
                }

                return null;
            },
            true,
            'data annotation requires it',
        ];

        yield [
            /**
             * @return Data|Setter|null
             */
            static function (string $class) {
                if (Data::class === $class) {
                    $data = new Data();
                    $data->fluent = false;

                    return $data;
                }

                if (Setter::class === $class) {
                    $setter = new Setter();
                    $setter->fluent = true;

                    return $setter;
                }

                return null;
            },
            true,
            'data annotation doesn\'t not require it and setter does',
        ];
    }

    /**
     * @param class-string|null $relation
     *
     * @dataProvider isNullableDataProvider
     * @testdox isNullable is $result when $testDox
     */
    public function testIsNullable(callable $callable, ?bool $propertyNullable, ?string $relation, bool $result, string $testDox): void
    {
        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getAnnotation')
            ->willReturnCallback($callable)
        ;
        if (!empty($relation)) {
            $property
                ->method('getRelation')
                ->willReturn(new $relation())
            ;
        }

        $propertyBehavior = $this->createMock(PropertyBehavior::class);
        $propertyBehavior
            ->method('isNullable')
            ->willReturn($propertyNullable)
        ;
        $setterBehavior = new SetterBehavior(
            $propertyBehavior,
            ['nullable' => true]
        );

        $this->assertSame($result, $setterBehavior->isNullable($property));
    }

    public function isNullableDataProvider(): iterable
    {
        yield [
            static function (string $class): void {},
            null,
            null,
            true,
            'no annotation',
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
            null,
            true,
            'setter annotation is not configured',
        ];

        yield [
            static function (string $class): ?Data {
                if (Data::class === $class) {
                    $data = new Data();
                    $data->nullable = false;

                    return $data;
                }

                return null;
            },
            null,
            null,
            false,
            'data annotation doesn\'t requires it',
        ];

        yield [
            static function (string $class): ?Data {
                if (Data::class === $class) {
                    $data = new Data();
                    $data->nullable = false;

                    return $data;
                }

                return null;
            },
            null,
            ManyToOneRelation::class,
            true,
            'doctrine relation requires it',
        ];

        yield [
            static function (string $class): ?Data {
                if (Data::class === $class) {
                    $data = new Data();
                    $data->nullable = true;

                    return $data;
                }

                return null;
            },
            false,
            null,
            false,
            'data annotation requires it but property doesn\'t',
        ];

        yield [
            /**
             * @return Data|Setter|null
             */
            static function (string $class) {
                if (Data::class === $class) {
                    $data = new Data();
                    $data->nullable = true;

                    return $data;
                }

                if (Setter::class === $class) {
                    $setter = new Setter();
                    $setter->nullable = false;

                    return $setter;
                }

                return null;
            },
            true,
            null,
            false,
            'data annotation requires it, setter doesn\'t and property does',
        ];
    }

    /**
     * @dataProvider hasToUpdateOtherSideDataProvider
     * @testdox hasToUpdateOtherSide is $result when $testDox
     */
    public function testHasToUpdateOtherSide(callable $callable, bool $result, string $testDox): void
    {
        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getAnnotation')
            ->willReturnCallback($callable)
        ;

        $setterBehavior = new SetterBehavior(
            $this->createMock(PropertyBehavior::class),
            ['updateOtherSide' => true]
        );

        $this->assertSame($result, $setterBehavior->hasToUpdateOtherSide($property));
    }

    public function hasToUpdateOtherSideDataProvider(): iterable
    {
        yield [
            static function (string $class): void {},
            true,
            'no annotation',
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
            true,
            'setter annotation is not configured',
        ];

        yield [
            static function (string $class): ?Data {
                if (Data::class === $class) {
                    $data = new Data();
                    $data->updateOtherSide = false;

                    return $data;
                }

                return null;
            },
            false,
            'data annotation doesn\'t requires it',
        ];

        yield [
            /**
             * @return Data|Setter|null
             */
            static function (string $class) {
                if (Data::class === $class) {
                    $data = new Data();
                    $data->updateOtherSide = true;

                    return $data;
                }

                if (Setter::class === $class) {
                    $setter = new Setter();
                    $setter->updateOtherSide = false;

                    return $setter;
                }

                return null;
            },
            false,
            'data annotation requires it and setter doesn\'t',
        ];
    }
}
