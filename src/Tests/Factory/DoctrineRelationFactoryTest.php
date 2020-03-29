<?php

namespace Mtarld\SymbokBundle\Tests\Factory;

use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Mtarld\SymbokBundle\Factory\DocBlockFactory;
use Mtarld\SymbokBundle\Factory\DoctrineRelationFactory;
use Mtarld\SymbokBundle\Finder\DocBlockFinder;
use Mtarld\SymbokBundle\Model\Relation\DoctrineRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt\Property;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @group unit
 * @group factory
 */
class DoctrineRelationFactoryTest extends TestCase
{
    /**
     * @param class-string|null $annotationClass
     *
     * @dataProvider buildDoctrineRelationCreation
     * @testdox Result is valid for $annotationClass
     */
    public function testDoctrineRelationCreation(
        ?string $annotationClass,
        ?string $targetEntity,
        ?string $inversedBy,
        ?string $mappedBy,
        ?bool $isOwning,
        ?string $targetClassName,
        ?string $targetPropertyName,
        ?string $targetGetterName,
        ?bool $resultOwning
    ): void {
        $finder = $this->createMock(DocBlockFinder::class);
        $factory = $this->createMock(DocBlockFactory::class);
        $factory
            ->method('createFor')
            ->willReturn(new DocBlock())
        ;

        $class = $this->createMock(SymbokClass::class);
        $class
            ->method('getName')
            ->willReturn('foo')
        ;

        $class
            ->method('getContext')
            ->willReturn(new Context(''))
        ;

        $property = $this->createMock(Property::class);

        if (!empty($annotationClass)) {
            $annotation = new $annotationClass();
            $annotation->targetEntity = $targetEntity;
            $annotation->inversedBy = $inversedBy;
            $annotation->mappedBy = $mappedBy;
            $annotation->isOwning = $isOwning;

            $finder
                ->method('findDoctrineRelation')
                ->willReturn($annotation)
            ;
        }

        $factory = new DoctrineRelationFactory(
            $factory,
            $finder,
            $this->createMock(LoggerInterface::class)
        );

        if (empty($annotationClass)) {
            $this->assertNull($factory->create($class, $property));

            return;
        }

        /** @var DoctrineRelation $relation */
        $relation = $factory->create($class, $property);

        $this->assertSame($targetClassName, $relation->getTargetClassName());
        $this->assertSame($targetPropertyName, $relation->getTargetPropertyName());
        $this->assertSame($resultOwning, $relation->isOwning());
        $this->assertSame($targetGetterName, $relation->getTargetGetterMethodName());
    }

    public function buildDoctrineRelationCreation(): iterable
    {
        yield [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        ];

        yield [
            ManyToOne::class,
            'Bar',
            'baz',
            null,
            null,
            'Bar',
            'baz',
            'getBaz',
            true,
        ];

        yield [
            ManyToOne::class,
            'Bar',
            null,
            null,
            null,
            'Bar',
            null,
            'getFoo',
            true,
        ];

        yield [
            ManyToMany::class,
            'Bar',
            'baz',
            null,
            true,
            'Bar',
            'baz',
            'getBaz',
            true,
        ];

        yield [
            OneToOne::class,
            'Bar',
            null,
            'baz',
            null,
            'Bar',
            'baz',
            'getBaz',
            false,
        ];

        yield [
            OneToMany::class,
            'Bar',
            null,
            'baz',
            null,
            'Bar',
            'baz',
            'getBaz',
            false,
        ];
    }
}
