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
     * @dataProvider resolveAnnotationsDataProvider
     * @testdox Annotation is kept when $testdox
     */
    public function testResolveAnnotations(Tag $tag, ?Context $context, array $namespaces, string $testdox): void
    {
        $docBlock = new DocBlock(
            '',
            null,
            [$tag],
            $context
        );

        $formatted = (new Formatter())->resolveAnnotations($docBlock, $namespaces);
        $this->assertCount(1, $formatted->getTags());
    }

    public function resolveAnnotationsDataProvider()
    {
        yield [
            new Generic("\A\Annotation"),
            null,
            ["\A"],
            'in namespace prefixed by \\',
        ];

        yield [
            new Generic("\A\Annotation"),
            null,
            [],
            'out of namespace',
        ];

        yield [
            new Generic("B\Annotation"),
            null,
            ["\B"],
            'in namespace not prefixed',
        ];

        yield [
            new Generic("B\Annotation"),
            new Context("\Somewhere"),
            ["\B"],
            'out of namespace using context',
        ];

        yield [
            new Generic("B\Annotation"),
            new Context("\Somewhere"),
            ["\Somewhere\B"],
            'in namespace using context',
        ];
    }

    public function testDocBlockIsConstitent()
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

        $formatted = $formatter->resolveAnnotations($docBlock, ["\A"]);
        $this->assertSame($docBlock->getSummary(), $formatted->getSummary());
        $this->assertSame($docBlock->getDescription(), $formatted->getDescription());
        $this->assertSame($docBlock->getContext(), $formatted->getContext());
    }
}
