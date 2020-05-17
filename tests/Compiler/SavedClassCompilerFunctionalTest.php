<?php

namespace Mtarld\SymbokBundle\Tests\Compiler;

use App\Entity\Product3;
use Mtarld\SymbokBundle\Compiler\SavedClassCompiler;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use Mtarld\SymbokBundle\Tests\KernelTestCase;

/**
 * @group functional
 * @group compiler
 */
class SavedClassCompilerFunctionalTest extends KernelTestCase
{
    public function testSavedClassIsCompiling(): void
    {
        /** @var SavedClassCompiler $compiler */
        $compiler = static::$container->get('symbok.compiler.saved');

        /** @var PhpCodeParser $codeFinder */
        $codeFinder = static::$container->get('symbok.parser.php_code');

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
