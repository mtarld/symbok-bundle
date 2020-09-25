<?php

namespace Mtarld\SymbokBundle\Tests\Parser\DocBlockParser;

use Mtarld\SymbokBundle\Parser\DocBlockParser\Formatter;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group parser
 */
class FormatterTest extends TestCase
{
    /**
     * @dataProvider formatAnnotationsDataProvider
     * @testdox Annotation is kept when $testdox
     */
    public function testFormatAnnotations(Tag $tag, ?Context $context, string $testdox): void
    {
        $docBlock = new DocBlock(
            '',
            null,
            [$tag],
            $context
        );

        $formatted = (new Formatter())->formatAnnotations($docBlock);
        $this->assertCount(1, $formatted->getTags());
    }

    public function formatAnnotationsDataProvider(): iterable
    {
        yield [
            new Generic("B\Annotation"),
            new Context("\Somewhere"),
            'out of namespace',
        ];

        yield [
            new Generic("B\Annotation"),
            new Context("\Somewhere"),
            'in namespace',
        ];
    }

    public function testDocBlockIsConsistent(): void
    {
        $docBlock = new DocBlock(
            'Summary',
            new Description('Description'),
            [
                new Generic("\A\Annotation"),
                new Generic("B\Annotation"),
            ],
            new Context("\Somewhere")
        );

        $formatter = new Formatter();

        $formatted = $formatter->formatAnnotations($docBlock);
        $this->assertSame($docBlock->getSummary(), $formatted->getSummary());
        $this->assertSame($docBlock->getDescription(), $formatted->getDescription());
        $this->assertSame($docBlock->getContext(), $formatted->getContext());
    }

    public function testNestedDescriptionTagsAreResolved(): void
    {
        $docBlock = new DocBlock(
            'Summary',
            new Description('Description'),
            [
                new Generic("\A\Annotation"),
                new Generic("B\Annotation", new Description('Description2', [
                    new Generic('C\Annotation', new Description('Description3', [
                        new Generic('\D\Annotation'),
                    ])),
                ])),
            ],
            new Context("\Somewhere")
        );

        $formatter = new Formatter();

        $formatted = $formatter->formatAnnotations($docBlock);
        $this->assertSame('A\Annotation', $formatted->getTags()[0]->getName());
        $this->assertSame('Somewhere\B\Annotation', $formatted->getTags()[1]->getName());
        $this->assertSame('Somewhere\C\Annotation', $formatted->getTags()[1]->getDescription()->getTags()[0]->getName());
        $this->assertSame('D\Annotation', $formatted->getTags()[1]->getDescription()->getTags()[0]->getDescription()->getTags()[0]->getName());
    }
}
