<?php

namespace Mtarld\SymbokBundle\Tests\Parser;

use Doctrine\Common\Annotations\AnnotationException;
use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\Factory\DocFactory;
use Mtarld\SymbokBundle\Parser\DocBlockParser;
use Mtarld\SymbokBundle\Parser\DocBlockParser\Formatter;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Since;
use PhpParser\Comment\Doc;
use PHPUnit\Framework\TestCase;

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
            ->method('formatAnnotations')
            ->willReturn($docBlock)
        ;

        $factory = $this->createMock(DocFactory::class);
        $factory
            ->method('createFromDocBlock')
            ->willReturn($doc)
        ;

        $parser = new DocBlockParser($formatter, $factory);

        $statements = $parser->parseAnnotations($docBlock);
        $this->assertInstanceOf(ToString::class, $statements[0]);
    }

    public function testParseUnknownAnnotation(): void
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
            ->method('formatAnnotations')
            ->willReturn($docBlock)
        ;

        $factory = $this->createMock(DocFactory::class);
        $factory
            ->method('createFromDocBlock')
            ->willReturn($doc)
        ;

        $parser = new DocBlockParser($formatter, $factory);

        $this->expectException(AnnotationException::class);
        $statements = $parser->parseAnnotations($docBlock);
    }

    public function testParseOutOfNamespaceAnnotation(): void
    {
        $docBlock = new DocBlock(
            '',
            null,
            [new Since('2.0')]
        );
        $doc = new Doc(
            '/** @author 2.0 */'
        );

        $formatter = $this->createMock(Formatter::class);
        $formatter
            ->method('formatAnnotations')
            ->willReturn($docBlock)
        ;

        $factory = $this->createMock(DocFactory::class);
        $factory
            ->method('createFromDocBlock')
            ->willReturn($doc)
        ;

        $parser = new DocBlockParser($formatter, $factory);

        $statements = $parser->parseAnnotations($docBlock);
        $this->assertCount(0, $statements);
    }
}
