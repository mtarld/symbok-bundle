<?php

namespace Mtarld\SymbokBundle\Tests\Compiler;

use Mtarld\SymbokBundle\Compiler\SavedClassCompiler;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Tests\Fixtures\files\Product3;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group functional
 * @group compiler
 */
class SavedClassCompilerFunctionalTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel();
    }

    public function testSavedClassIsCompiling(): void
    {
        /** @var SavedClassCompiler $compiler */
        $compiler = static::$container->get(SavedClassCompiler::class);

        /** @var PhpCodeParser $codeFinder */
        $codeFinder = static::$container->get(PhpCodeParser::class);

        $statements = $codeFinder->parseStatements(Product3::class);

        $tags = $compiler->compile($statements)->getDocBlock()->getTags();
        $tags = array_filter($tags, function (Tag $tag) {
            return $tag instanceof Method;
        });
        $tagNames = array_map(function (Method $method) {
            return $method->getMethodName();
        }, $tags);

        $this->assertContains('__toString', $tagNames);
        $this->assertContains('__construct', $tagNames);
        $this->assertContains('getNbCall', $tagNames);
        $this->assertContains('getId', $tagNames);
        $this->assertContains('setId', $tagNames);
    }
}
