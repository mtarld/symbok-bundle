<?php

namespace Mtarld\SymbokBundle\Tests\Behavior;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Nullable;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Behavior\PropertyBehavior;
use Mtarld\SymbokBundle\Model\Relation\DoctrineRelation;
use Mtarld\SymbokBundle\Model\Relation\ManyToManyRelation;
use Mtarld\SymbokBundle\Model\Relation\OneToOneRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group behavior
 */
class PropertyBehaviorTest extends TestCase
{
    /**
     * @dataProvider isNullableDataProvider
     * @testdox isNullable is $result when $testDox
     */
    public function testIsNullable(callable $callable, ?DoctrineRelation $relation, ?bool $result, string $testDox): void
    {
        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getAnnotation')
            ->will($this->returnCallback($callable))
        ;
        $property
            ->method('getRelation')
            ->willReturn($relation)
        ;

        $this->assertSame($result, (new PropertyBehavior())->isNullable($property));
    }

    public function isNullableDataProvider(): iterable
    {
        yield [
            function ($class) {
                return null;
            },
            null,
            null,
            'no annotation and no relation',
        ];

        yield [
            function ($class) {
                if (JoinColumn::class === $class) {
                    $annotation = new JoinColumn();
                    $annotation->nullable = true;

                    return $annotation;
                }

                return null;
            },
            null,
            true,
            'doctrine join column requires it',
        ];

        yield [
            function ($class) {
                if (JoinColumn::class === $class) {
                    $annotation = new JoinColumn();
                    $annotation->nullable = false;

                    return $annotation;
                }

                if (Column::class === $class) {
                    $annotation = new Column();
                    $annotation->nullable = true;

                    return $annotation;
                }

                return null;
            },
            null,
            true,
            'doctrine column requires it and join column doesn\'t',
        ];

        yield [
            function ($class) {
                if (JoinColumn::class === $class) {
                    $annotation = new JoinColumn();
                    $annotation->nullable = false;

                    return $annotation;
                }

                if (Column::class === $class) {
                    $annotation = new Column();
                    $annotation->nullable = false;

                    return $annotation;
                }

                if (Nullable::class === $class) {
                    $annotation = new Nullable();
                    $annotation->nullable = true;

                    return $annotation;
                }

                return null;
            },
            null,
            true,
            'nullable requires it and others don\'t',
        ];

        yield [
            function ($class) {
                return null;
            },
            new OneToOneRelation(),
            true,
            'no annotation and single relation',
        ];

        yield [
            function ($class) {
                return null;
            },
            new ManyToManyRelation(),
            false,
            'no annotation and collection relation',
        ];
    }

    /**
     * @dataProvider requireDataProvider
     * @testdox $method is $result when $testDox
     */
    public function testRequire(string $method, callable $classAnnotation, callable $propertyAnnotation, bool $result, string $testDox): void
    {
        $class = $this->createMock(SymbokClass::class);
        $class
            ->method('getAnnotation')
            ->will($this->returnCallback($classAnnotation))
        ;
        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getClass')
            ->willReturn($class)
        ;
        $property
            ->method('getAnnotation')
            ->will($this->returnCallback($propertyAnnotation))
        ;

        $this->assertSame($result, (new PropertyBehavior())->{$method}($property));
    }

    public function requireDataProvider(): iterable
    {
        yield [
            'requireGetter',
            function () {
                return null;
            },
            function () {
                return null;
            },
            false,
            'no annotation',
        ];
        yield [
            'requireGetter',
            function () {
                return null;
            },
            function () {
                return new Getter();
            },
            true,
            'getter annotation',
        ];
        yield [
            'requireGetter',
            function () {
                return new Data();
            },
            function () {
                return null;
            },
            true,
            'data annotation',
        ];

        yield [
            'requireSetter',
            function () {
                return null;
            },
            function () {
                return null;
            },
            false,
            'no annotation',
        ];
        yield [
            'requireSetter',
            function () {
                return null;
            },
            function () {
                return new Setter();
            },
            true,
            'setter annotation',
        ];
        yield [
            'requireSetter',
            function () {
                return new Data();
            },
            function () {
                return null;
            },
            true,
            'data annotation',
        ];

        yield [
            'requireAdder',
            function () {
                return null;
            },
            function () {
                return null;
            },
            false,
            'no annotation',
        ];
        yield [
            'requireAdder',
            function () {
                return null;
            },
            function () {
                $annotation = new Setter();
                $annotation->add = true;

                return $annotation;
            },
            true,
            'setter annotation with add allowed',
        ];
        yield [
            'requireAdder',
            function () {
                return null;
            },
            function () {
                $annotation = new Setter();
                $annotation->add = false;

                return $annotation;
            },
            false,
            'setter annotation with add forbidden',
        ];
        yield [
            'requireAdder',
            function () {
                $annotation = new Data();
                $annotation->add = true;

                return $annotation;
            },
            function () {
                return null;
            },
            true,
            'data annotation with add allowed',
        ];
        yield [
            'requireAdder',
            function () {
                $annotation = new Data();
                $annotation->add = false;

                return $annotation;
            },
            function () {
                return null;
            },
            false,
            'data annotation with add forbidden',
        ];

        yield [
            'requireRemover',
            function () {
                return null;
            },
            function () {
                return null;
            },
            false,
            'no annotation',
        ];
        yield [
            'requireRemover',
            function () {
                return null;
            },
            function () {
                $annotation = new Setter();
                $annotation->remove = true;

                return $annotation;
            },
            true,
            'setter annotation with remove allowed',
        ];
        yield [
            'requireRemover',
            function () {
                return null;
            },
            function () {
                $annotation = new Setter();
                $annotation->remove = false;

                return $annotation;
            },
            false,
            'setter annotation with remove forbidden',
        ];
        yield [
            'requireRemover',
            function () {
                $annotation = new Data();
                $annotation->remove = true;

                return $annotation;
            },
            function () {
                return null;
            },
            true,
            'data annotation with remove allowed',
        ];
        yield [
            'requireRemover',
            function () {
                $annotation = new Data();
                $annotation->remove = false;

                return $annotation;
            },
            function () {
                return null;
            },
            false,
            'data annotation with remove forbidden',
        ];
    }
}
