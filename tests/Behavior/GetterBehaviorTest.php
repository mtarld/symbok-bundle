<?php

namespace Mtarld\SymbokBundle\Tests\Behavior;

use Doctrine\ORM\Mapping\Column;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Behavior\GetterBehavior;
use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group behavior
 */
class GetterBehaviorTest extends TestCase
{
    /**
     * @dataProvider isNullableDataProvider
     * @testdox $testDox
     */
    public function testIsNullable(callable $callable, ?bool $propertyNullable, bool $result, string $testDox): void
    {
        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getAnnotation')
            ->willReturnCallback($callable)
        ;

        $propertyBehavior = $this->createMock(PropertyBehavior::class);
        $propertyBehavior
            ->method('isNullable')
            ->willReturn($propertyNullable)
        ;
        $getterBehavior = new GetterBehavior(
            $propertyBehavior,
            ['nullable' => true]
        );

        $this->assertSame($result, $getterBehavior->isNullable($property));
    }

    public function isNullableDataProvider(): iterable
    {
        yield [
            static function (string $class): void {},
            null,
            true,
            'Default is ok',
        ];

        yield [
            static function (string $class): ?Getter {
                if (Getter::class === $class) {
                    return new Getter();
                }

                return null;
            },
            null,
            true,
            'Getter without nullable is falling back to default',
        ];

        yield [
            /**
             * @return Getter|Column|null
             */
            static function (string $class) {
                if (Getter::class === $class) {
                    $getter = new Getter();
                    $getter->nullable = true;

                    return $getter;
                }

                if (Column::class === $class) {
                    return new Column();
                }

                return null;
            },
            null,
            true,
            'Getter is prior to doctrine',
        ];

        yield [
            /**
             * @return Getter|Column|null
             */
            static function (string $class) {
                if (Getter::class === $class) {
                    return new Getter();
                }

                if (Column::class === $class) {
                    return new Column();
                }

                return null;
            },
            null,
            true,
            'Doctrine is ok',
        ];

        yield [
            /**
             * @return Column|Data|null
             */
            static function (string $class) {
                if (Column::class === $class) {
                    return new Column();
                }

                if (Data::class === $class) {
                    $data = new Data();
                    $data->nullable = false;

                    return $data;
                }

                return null;
            },
            null,
            true,
            'Doctrine is prior to data',
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
            null,
            true,
            'Data with nullable is ok',
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
            true,
            true,
            'Property is prior to data',
        ];

        yield [
            /**
             * @return Data|Getter|null
             */
            static function (string $class) {
                if (Data::class === $class) {
                    $data = new Data();
                    $data->nullable = false;

                    return $data;
                }

                if (Getter::class === $class) {
                    $getter = new Getter();
                    $getter->nullable = true;

                    return $getter;
                }

                return null;
            },
            false,
            true,
            'Getter is prior to property and data',
        ];
    }

    /**
     * @dataProvider hasHasPrefixDataProvider
     * @testdox has prefix is $result for $type and $hasPrefix hasPrefix annotation property
     */
    public function testHasHasPrefix(?Type $type, bool $hasPrefix, bool $result): void
    {
        $annotation = new Getter();
        $annotation->hasPrefix = $hasPrefix;

        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getType')
            ->willReturn($type)
        ;
        $property
            ->method('getAnnotation')
            ->willReturn($annotation)
        ;

        $propertyBehavior = $this->createMock(PropertyBehavior::class);

        $getterBehavior = new GetterBehavior(
            $propertyBehavior,
            [
                'getter' => [],
            ]
        );

        $this->assertSame($result, $getterBehavior->hasHasPrefix($property));
    }

    public function hasHasPrefixDataProvider(): iterable
    {
        yield [new Boolean(), false, false];
        yield [new Integer(), true, false];
        yield [new Boolean(), true, true];
    }
}
