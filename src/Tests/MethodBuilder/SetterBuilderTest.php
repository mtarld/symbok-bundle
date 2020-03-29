<?php

namespace Mtarld\SymbokBundle\Tests\MethodBuilder;

use Mtarld\SymbokBundle\Behavior\SetterBehavior;
use Mtarld\SymbokBundle\MethodBuilder\SetterBuilder;
use Mtarld\SymbokBundle\Model\Relation\OneToManyRelation;
use Mtarld\SymbokBundle\Model\Relation\OneToOneRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\MethodManipulator;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use phpDocumentor\Reflection\Type;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group builder
 */
class SetterBuilderTest extends TestCase
{
    /**
     * @param class-string|null $relation
     *
     * @dataProvider buildExpectedContentDataProvider
     * @testdox Build expected content with $propertyType property type, $fluent fluent, $otherSide updateOtherSide, $relation relation and $owning owning
     */
    public function testBuildExpectedContent(?string $propertyType, bool $fluent, bool $otherSide, ?string $relation, bool $owning, string $result): void
    {
        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getName')
            ->willReturn('name')
        ;

        if (!empty($relation)) {
            $relation = new $relation();
            $relation->setTargetPropertyName('target');
            $relation->setIsOwning($owning);

            $property
                ->method('getRelation')
                ->willReturn($relation)
            ;
        }

        $behavior = $this->createMock(SetterBehavior::class);
        $behavior
            ->method('isFluent')
            ->willReturn($fluent)
        ;
        $behavior
            ->method('hasToUpdateOtherSide')
            ->willReturn($otherSide)
        ;

        $typeFormatter = $this->createMock(TypeFormatter::class);
        $typeFormatter
            ->method('asPhpString')
            ->willReturn($propertyType)
        ;

        $builder = new SetterBuilder(
            $behavior,
            new MethodManipulator(),
            $typeFormatter
        );

        $method = $builder->build($property);
        $methodCode = (new Standard())->prettyPrint([$method]);

        // Remove void when not supported by PHP version
        if (PHP_VERSION_ID < 70100) {
            $methodCode = preg_replace('/ : void/', '', $methodCode);
        }

        $this->assertSame($result, $methodCode);
    }

    public function buildExpectedContentDataProvider(): iterable
    {
        yield [
            '?string',
            false,
            true,
            null,
            false,
            'public function setName(?string $name) : void
{
    $this->name = $name;
}',
        ];

        yield [
            null,
            false,
            true,
            null,
            false,
            'public function setName($name) : void
{
    $this->name = $name;
}',
        ];

        yield [
            null,
            true,
            true,
            null,
            false,
            'public function setName($name) : self
{
    $this->name = $name;
    return $this;
}',
        ];

        yield [
            null,
            false,
            true,
            OneToOneRelation::class,
            false,
            'public function setName($name) : void
{
    $this->name = $name;
    $new = null === $name ? null : $this;
    if ($name->getTarget() !== $new) {
        $name->setTarget($new);
    }
}',
        ];

        yield [
            null,
            false,
            false,
            OneToOneRelation::class,
            false,
            'public function setName($name) : void
{
    $this->name = $name;
}',
        ];

        yield [
            null,
            true,
            true,
            OneToOneRelation::class,
            false,
            'public function setName($name) : self
{
    $this->name = $name;
    $new = null === $name ? null : $this;
    if ($name->getTarget() !== $new) {
        $name->setTarget($new);
    }
    return $this;
}',
        ];

        yield [
            null,
            false,
            true,
            OneToManyRelation::class,
            false,
            'public function setName($name) : void
{
    $this->name = $name;
}',
        ];

        yield [
            null,
            false,
            true,
            OneToOneRelation::class,
            true,
            'public function setName($name) : void
{
    $this->name = $name;
}',
        ];
    }
}
