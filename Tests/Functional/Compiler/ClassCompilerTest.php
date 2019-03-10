<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Compiler;

use Mtarld\SymbokBundle\Compiler\ClassCompiler;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;

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
        $this->assertEquals(sizeof($stmts) + 4, sizeof($this->nodeClass->stmts));

        $filePath = __DIR__ . '/../../Fixtures/files/Product3.php';
        $this->buildContext($filePath);
        $stmts = $this->nodeClass->stmts;

        $compiler->compile($this->nodeClass);
        $this->assertEquals(sizeof($stmts) + 2, sizeof($this->nodeClass->stmts));
    }
}
