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
            ->will($this->returnCallback($callable))
        ;

        $propertyBehavior = $this->createMock(PropertyBehavior::class);
        $setterBehavior = new SetterBehavior(
            $propertyBehavior,
            [
                'defaults' => [
                    'setter' => ['fluent' => false],
                ],
            ]
        );

        $this->assertSame($result, $setterBehavior->isFluent($property));
    }

    public function isFluentDataProvider(): iterable
    {
        yield [
            function ($class) {
                return null;
            },
            false,
            'no annotation',
        ];

        yield [
            function ($class) {
                return new $class();
            },
            false,
            'setter annotation',
        ];

        yield [
            function ($class) {
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
            function ($class) {
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
     * @dataProvider isNullableDataProvider
     * @testdox isNullable is $result when $testDox
     */
    public function testIsNullable(callable $callable, ?bool $propertyNullable, ?string $relation, bool $result, string $testDox): void
    {
        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getAnnotation')
            ->will($this->returnCallback($callable))
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
            [
                'defaults' => [
                    'setter' => ['nullable' => true],
                ],
            ]
        );

        $this->assertSame($result, $setterBehavior->isNullable($property));
    }

    public function isNullableDataProvider(): iterable
    {
        yield [
            function ($class) {
                return null;
            },
            null,
            null,
            true,
            'no annotation',
        ];

        yield [
            function ($class) {
                return new $class();
            },
            null,
            null,
            true,
            'setter annotation is not configured',
        ];

        yield [
            function ($class) {
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
            function ($class) {
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
            function ($class) {
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
            function ($class) {
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
            ->will($this->returnCallback($callable))
        ;

        $setterBehavior = new SetterBehavior(
            $this->createMock(PropertyBehavior::class),
            [
                'defaults' => [
                    'setter' => ['updateOtherSide' => true],
                ],
            ]
        );

        $this->assertSame($result, $setterBehavior->hasToUpdateOtherSide($property));
    }

    public function hasToUpdateOtherSideDataProvider(): iterable
    {
        yield [
            function ($class) {
                return null;
            },
            true,
            'no annotation',
        ];

        yield [
            function ($class) {
                return new $class();
            },
            true,
            'setter annotation is not configured',
        ];

        yield [
            function ($class) {
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
            function ($class) {
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
