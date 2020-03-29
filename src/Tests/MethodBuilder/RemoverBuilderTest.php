<?php

namespace Mtarld\SymbokBundle\Tests\MethodBuilder;

use Mtarld\SymbokBundle\Behavior\SetterBehavior;
use Mtarld\SymbokBundle\MethodBuilder\RemoverBuilder;
use Mtarld\SymbokBundle\MethodBuilder\RemoverBuilder\DoctrineStatements;
use Mtarld\SymbokBundle\MethodBuilder\RemoverBuilder\RegularStatements;
use Mtarld\SymbokBundle\Model\Relation\ManyToManyRelation;
use Mtarld\SymbokBundle\Model\Relation\ManyToOneRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\MethodManipulator;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group builder
 */
class RemoverBuilderTest extends TestCase
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
            ->willReturn('names')
        ;

        if (!empty($relation)) {
            $relation = new $relation();
            $relation->setTargetPropertyName('target');
            $relation->setTargetClassName('Obj');
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
            ->method('nestedAsPhpString')
            ->willReturn($propertyType)
        ;

        $builder = new RemoverBuilder(
            $behavior,
            new MethodManipulator(),
            $typeFormatter,
            new RegularStatements(),
            new DoctrineStatements($behavior)
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
            'int',
            false,
            true,
            null,
            false,
            'public function removeName(int $name) : void
{
    $key = array_search($name, $this->names, true);
    if (false !== $key) {
        unset($this->names[$key]);
    }
}',
        ];

        yield [
            null,
            true,
            true,
            null,
            false,
            'public function removeName($name) : self
{
    $key = array_search($name, $this->names, true);
    if (false !== $key) {
        unset($this->names[$key]);
    }
    return $this;
}',
        ];

        yield [
            null,
            false,
            true,
            ManyToOneRelation::class,
            true,
            'public function removeName(Obj $name) : void
{
    if ($this->names->contains($name)) {
        $this->names->removeElement($name);
        if ($name->getTarget() === $this) {
            $name->setTarget(null);
        }
    }
}',
        ];

        yield [
            null,
            false,
            false,
            ManyToOneRelation::class,
            true,
            'public function removeName(Obj $name) : void
{
    if ($this->names->contains($name)) {
        $this->names->removeElement($name);
    }
}',
        ];

        yield [
            null,
            false,
            true,
            ManyToManyRelation::class,
            false,
            'public function removeName(Obj $name) : void
{
    if ($this->names->contains($name)) {
        $this->names->removeElement($name);
    }
}',
        ];

        yield [
            null,
            false,
            true,
            ManyToManyRelation::class,
            true,
            'public function removeName(Obj $name) : void
{
    if ($this->names->contains($name)) {
        $this->names->removeElement($name);
        $name->removeTarget($this);
    }
}',
        ];

        yield [
            null,
            false,
            false,
            ManyToManyRelation::class,
            true,
            'public function removeName(Obj $name) : void
{
    if ($this->names->contains($name)) {
        $this->names->removeElement($name);
    }
}',
        ];
    }
}
