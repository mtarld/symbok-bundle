<?php

namespace Mtarld\SymbokBundle\Tests\Parser;

use Doctrine\Common\Annotations\AnnotationException;
use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\Factory\DocFactory;
use Mtarld\SymbokBundle\Parser\DocBlockParser;
use Mtarld\SymbokBundle\Parser\DocBlockParser\Formatter;
use Mtarld\SymbokBundle\Repository\AnnotationRepository;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use PhpParser\Comment\Doc;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group unit
 * @group parser
 */
class DocBlockParserTest extends TestCase
{
    public function testParseAnnotations(): void
    {
        $docBlock = new DocBlock(
            '',
            null,
            [new Generic(ToString::class)]
        );
        $doc = new Doc(
            "/** @\Mtarld\SymbokBundle\Annotation\ToString */"
        );

        $formatter = $this->createMock(Formatter::class);
        $formatter
            ->method('cleanAnnotations')
            ->willReturn($docBlock)
        ;

        $factory = $this->createMock(DocFactory::class);
        $factory
            ->method('createFromDocBlock')
            ->willReturn($doc)
        ;

        $repository = $this->createMock(AnnotationRepository::class);
        $repository
            ->method('findNamespaces')
            ->willReturn([(new ReflectionClass(ToString::class))->getNamespaceName()])
        ;
        $repository
            ->method('findAll')
            ->willReturn([ToString::class])
        ;

        $parser = new DocBlockParser(
            $formatter,
            $factory,
            $repository
        );
        $statements = $parser->parseAnnotations($docBlock);

        $this->assertInstanceOf(ToString::class, $statements[0]);
    }

    public function testParseAnnotationsOutOfNamespace(): void
    {
        $docBlock = new DocBlock(
            '',
            null,
            [new Generic("\Mtarld\SymbokBundle\Annotation\Unknown")]
        );
        $doc = new Doc(
            "/** @\Mtarld\SymbokBundle\Annotation\Unknown */"
        );

        $formatter = $this->createMock(Formatter::class);
        $formatter
            ->method('cleanAnnotations')
            ->willReturn($docBlock)
        ;

        $factory = $this->createMock(DocFactory::class);
        $factory
            ->method('createFromDocBlock')
            ->willReturn($doc)
        ;

        $repository = $this->createMock(AnnotationRepository::class);

        $parser = new DocBlockParser(
            $formatter,
            $factory,
            $repository
        );

        $this->expectException(AnnotationException::class);
        $statements = $parser->parseAnnotations($docBlock);
    }
}
