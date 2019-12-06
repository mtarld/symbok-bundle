<?php

namespace Mtarld\SymbokBundle\Tests\Finder;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Mtarld\SymbokBundle\Annotation\AnnotationInterface;
use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\Finder\DocBlock\DoctrineTypes;
use Mtarld\SymbokBundle\Finder\DocBlockFinder;
use Mtarld\SymbokBundle\Parser\DocBlockParser;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @group unit
 * @group finder
 */
class DocBlockFinderTest extends TestCase
{
    public function testFindAnnotations(): void
    {
        $parser = $this->createMock(DocBlockParser::class);
        $parser
            ->method('parseAnnotations')
            ->willReturn([new ToString()])
        ;

        $finder = new DocBlockFinder(
            $parser,
            $this->createMock(DoctrineTypes::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertContains(
            ToString::class,
            array_map(
                function (AnnotationInterface $annotation) {
                    return get_class($annotation);
                },
                $finder->findAnnotations(new DocBlock())
            )
        );
    }

    public function testFindDoctrineRelation(): void
    {
        $parser = $this->createMock(DocBlockParser::class);
        $parser
            ->method('parseAnnotations')
            ->willReturn([
                new ToString(),
                new OneToOne(),
            ])
        ;

        $finder = new DocBlockFinder(
            $parser,
            $this->createMock(DoctrineTypes::class),
            $this->createMock(LoggerInterface::class)
        );

        $relation = $finder->findDoctrineRelation(new DocBlock());

        $this->assertSame(OneToOne::class, get_class($relation));

        $parser = $this->createMock(DocBlockParser::class);
        $parser
            ->method('parseAnnotations')
            ->willReturn([
                new ToString(),
            ])
        ;

        $finder = new DocBlockFinder(
            $parser,
            $this->createMock(DoctrineTypes::class),
            $this->createMock(LoggerInterface::class)
        );

        $relation = $finder->findDoctrineRelation(new DocBlock());

        $this->assertNull($relation);
    }

    /**
     * @testdox Can find type from var type
     */
    public function testFindTypeFromVarTag(): void
    {
        $finder = new DocBlockFinder(
            $this->createMock(DocBlockParser::class),
            $this->createMock(DoctrineTypes::class),
            $this->createMock(LoggerInterface::class)
        );

        $docBlock = new DocBlock(
            '',
            null,
            [new Var_('test', new Integer())]
        );

        $this->assertInstanceOf(Integer::class, $finder->findType($docBlock));

        $docBlock = new DocBlock(
            '',
            null,
            [new Var_('test', new Compound([]))]
        );

        $this->assertInstanceOf(Mixed_::class, $finder->findType($docBlock));
    }

    /**
     * @testdox Var tag found type can be nullable
     */
    public function testFindTypeFromVarTagNullable(): void
    {
        $docBlock = new DocBlock(
            '',
            null,
            [new Var_('test', new Nullable(new Integer()))]
        );

        $finder = new DocBlockFinder(
            $this->createMock(DocBlockParser::class),
            $this->createMock(DoctrineTypes::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertInstanceOf(Integer::class, $finder->findType($docBlock));
    }

    /**
     * @testdox Var tag found type can be array
     */
    public function testFindTypeFromVarTagArray(): void
    {
        $docBlock = new DocBlock(
            '',
            null,
            [new Var_('test', new Array_(new Integer()))]
        );

        $finder = new DocBlockFinder(
            $this->createMock(DocBlockParser::class),
            $this->createMock(DoctrineTypes::class),
            $this->createMock(LoggerInterface::class)
        );

        $type = $finder->findType($docBlock);

        $this->assertInstanceOf(Array_::class, $type);
        $this->assertInstanceOf(Integer::class, $type->getValueType());
    }

    /**
     * @dataProvider findTypeFromDoctrineRelationDataProvider
     * @testdox Doctrine relation found type is $fqsen with $testdox
     */
    public function testFindTypeFromDoctrineRelation($annotation, bool $isNull, ?string $fqsen, string $testdox): void
    {
        $parser = $this->createMock(DocBlockParser::class);
        $parser
            ->method('parseAnnotations')
            ->willReturn([$annotation])
        ;

        $types = $this->createMock(DoctrineTypes::class);

        $finder = new DocBlockFinder(
            $parser,
            $this->createMock(DoctrineTypes::class),
            $this->createMock(LoggerInterface::class)
        );

        $type = $finder->findType(new DocBlock(''));

        if (true === $isNull) {
            $this->assertNull($type);

            return;
        }

        $this->assertInstanceOf(Object_::class, $type);
        $this->assertSame($fqsen, (string) $type->getFqsen());
    }

    public function findTypeFromDoctrineRelationDataProvider(): iterable
    {
        $manyToOneWithTarget = new ManyToOne();
        $manyToOneWithTarget->targetEntity = 'Target';

        yield [
            new OneToMany(),
            false,
            '\\'.Collection::class,
            'OneToMany annotation without target',
        ];

        yield [
            $manyToOneWithTarget,
            false,
            '\\Target',
            'ManyToOne annotation with target',
        ];

        yield [
            new ManyToOne(),
            true,
            null,
            'ManyToOne annotation without target',
        ];
    }

    /**
     * @dataProvider findTypeFromDoctrineColumnDataProvider
     * @testdox Doctrine column found type is $fqsen when $testdox and type is $type
     */
    public function testFindTypeFromDoctrineColumn(string $type, array $typeMap, bool $isNull, ?string $fqsen, string $testdox): void
    {
        $annotation = new Column();
        $annotation->type = $type;

        $parser = $this->createMock(DocBlockParser::class);
        $parser
            ->method('parseAnnotations')
            ->willReturn([$annotation])
            ;

        $types = $this->createMock(DoctrineTypes::class);
        $types
            ->method('getTypeMap')
            ->willReturn($typeMap)
        ;

        $finder = new DocBlockFinder(
            $parser,
            $types,
            $this->createMock(LoggerInterface::class)
        );

        $type = $finder->findType(new DocBlock(''));

        if (true === $isNull) {
            $this->assertNull($type);

            return;
        }

        $this->assertInstanceOf($fqsen, $type);
    }

    public function findTypeFromDoctrineColumnDataProvider(): iterable
    {
        yield [
            'string',
            [Type::STRING => new String_()],
            false,
            String_::class,
            'type is in type map',
        ];

        yield [
            'string',
            [],
            true,
            null,
            'type map is empty',
        ];

        yield [
            'unknown',
            [Type::STRING => new String_()],
            true,
            null,
            'type is not in type map',
        ];
    }
}
