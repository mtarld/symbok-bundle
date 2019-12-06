<?php

namespace Mtarld\SymbokBundle\Tests\MethodBuilder;

use Mtarld\SymbokBundle\Behavior\SetterBehavior;
use Mtarld\SymbokBundle\MethodBuilder\AdderBuilder;
use Mtarld\SymbokBundle\MethodBuilder\AdderBuilder\DoctrineStatements;
use Mtarld\SymbokBundle\MethodBuilder\AdderBuilder\RegularStatements;
use Mtarld\SymbokBundle\Model\Relation\ManyToManyRelation;
use Mtarld\SymbokBundle\Model\Relation\OneToManyRelation;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\MethodManipulator;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group builder
 */
class AdderBuilderTest extends TestCase
{
    /**
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

            if ($relation instanceof ManyToManyRelation) {
                $relation->setIsOwning($owning);
            }

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
            ->method('nestedAsString')
            ->willReturn($propertyType)
        ;

        $builder = new AdderBuilder(
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
            'public function addName(int $name) : void
{
    $this->names[] = $name;
}',
        ];

        yield [
            null,
            true,
            true,
            null,
            false,
            'public function addName($name) : self
{
    $this->names[] = $name;
    return $this;
}',
        ];

        yield [
            null,
            false,
            true,
            OneToManyRelation::class,
            false,
            'public function addName(Obj $name) : void
{
    if (!$this->names->contains($name)) {
        $this->names->add($name);
    }
}',
        ];

        yield [
            null,
            false,
            true,
            ManyToManyRelation::class,
            false,
            'public function addName(Obj $name) : void
{
    if (!$this->names->contains($name)) {
        $this->names->add($name);
    }
}',
        ];

        yield [
            null,
            false,
            true,
            ManyToManyRelation::class,
            true,
            'public function addName(Obj $name) : void
{
    if (!$this->names->contains($name)) {
        $this->names->add($name);
        $name->addTarget($this);
    }
}',
        ];

        yield [
            null,
            false,
            false,
            ManyToManyRelation::class,
            true,
            'public function addName(Obj $name) : void
{
    if (!$this->names->contains($name)) {
        $this->names->add($name);
    }
}',
        ];
    }
}
