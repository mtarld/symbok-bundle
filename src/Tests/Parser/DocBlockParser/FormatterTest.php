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
     * @dataProvider cleanAnnotationsDataProvider
     * @testdox Annotation kept is $shouldStay when $testdox
     */
    public function testCleanAnnotations(Tag $tag, ?Context $context, array $namespaces, bool $shouldStay, string $testdox): void
    {
        $docBlock = new DocBlock(
            '',
            null,
            [$tag],
            $context
        );

        $formatted = (new Formatter())->cleanAnnotations($docBlock, $namespaces);
        $this->assertCount((int) $shouldStay, $formatted->getTags());
    }

    public function cleanAnnotationsDataProvider()
    {
        yield [
            new Generic("\A\Annotation"),
            null,
            ["\A"],
            true,
            'in namespace prefixed by \\',
        ];

        yield [
            new Generic("\A\Annotation"),
            null,
            [],
            false,
            'out of namespace',
        ];

        yield [
            new Generic("B\Annotation"),
            null,
            ["\B"],
            true,
            'in namespace not prefixed',
        ];

        yield [
            new Generic("B\Annotation"),
            new Context("\Somewhere"),
            ["\B"],
            false,
            'out of namespace using context',
        ];

        yield [
            new Generic("B\Annotation"),
            new Context("\Somewhere"),
            ["\Somewhere\B"],
            true,
            'in namespace using context',
        ];
    }

    public function testCleanAnnotationsDocBlockConstitency()
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

        $formatted = $formatter->cleanAnnotations($docBlock, ["\A"]);
        $this->assertSame($docBlock->getSummary(), $formatted->getSummary());
        $this->assertSame($docBlock->getDescription(), $formatted->getDescription());
        $this->assertSame($docBlock->getContext(), $formatted->getContext());
    }
}
