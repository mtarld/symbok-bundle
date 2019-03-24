<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Compiler;

use Mtarld\SymbokBundle\Compiler\ClassCompiler;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;
use PhpParser\PrettyPrinter\Standard;

class ClassCompilerTest extends AbstractFunctionalTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadAnnotations();
    }

    public function testCompile()
    {
        /** @var ClassCompiler $compiler */
        $compiler = self::$container->get(ClassCompiler::class);

        $filePath = __DIR__ . '/../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);
        $stmts = $this->nodeClass->stmts;

        $compiler->compile($this->nodeClass);
        $this->assertEquals(sizeof($stmts) + 4, sizeof($this->nodeClass->stmts));

        $filePath = __DIR__ . '/../../Fixtures/files/Product2.php';
        $this->buildContext($filePath);
        $stmts = $this->nodeClass->stmts;

        $compiler->compile($this->nodeClass);
        $this->assertEquals(sizeof($stmts) + 5, sizeof($this->nodeClass->stmts));

        $filePath = __DIR__ . '/../../Fixtures/files/Product3.php';
        $this->buildContext($filePath);
        $stmts = $this->nodeClass->stmts;

        $compiler->compile($this->nodeClass);
        $this->assertEquals(sizeof($stmts) + 10, sizeof($this->nodeClass->stmts));
    }

    public function testCompiledPhp()
    {
        /** @var ClassCompiler $compiler */
        $compiler = self::$container->get(ClassCompiler::class);

        $serializer = new Standard();

        $filePath = __DIR__ . '/../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);
        $compiler->compile($this->nodeClass);

        $generatedPhp = $serializer->prettyPrint($this->nodeClass->stmts);
        $wantedFilePath = __DIR__ . '/../../Fixtures/generated/Product1.php';

        $this->assertStringEqualsFile($wantedFilePath, $generatedPhp);

        $filePath = __DIR__ . '/../../Fixtures/files/Product2.php';
        $this->buildContext($filePath);
        $compiler->compile($this->nodeClass);

        $generatedPhp = $serializer->prettyPrint($this->nodeClass->stmts);
        $wantedFilePath = __DIR__ . '/../../Fixtures/generated/Product2.php';

        $this->assertStringEqualsFile($wantedFilePath, $generatedPhp);

        $filePath = __DIR__ . '/../../Fixtures/files/Product3.php';
        $this->buildContext($filePath);
        $compiler->compile($this->nodeClass);

        $generatedPhp = $serializer->prettyPrint($this->nodeClass->stmts);
        $wantedFilePath = __DIR__ . '/../../Fixtures/generated/Product3.php';

        $this->assertStringEqualsFile($wantedFilePath, $generatedPhp);
    }
}
