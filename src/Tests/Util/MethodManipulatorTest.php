<?php

namespace Mtarld\SymbokBundle\Tests\Util;

use Mtarld\SymbokBundle\Util\MethodManipulator;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Void_;
use PhpParser\Builder\Method;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group util
 */
class MethodManipulatorTest extends TestCase
{
    public function testMakeFluent(): void
    {
        $builder = new Method('method');
        (new MethodManipulator())->makeFluent($builder);

        $returnStmt = $builder->getNode()->getStmts()[0];

        $this->assertInstanceOf(Return_::class, $returnStmt);
        $this->assertEquals(new Variable('this'), $returnStmt->expr);

        $this->assertSame((string) new Self_(), (string) $builder->getNode()->getReturnType());
    }

    public function testMakeVoidReturn(): void
    {
        $builder = new Method('method');
        (new MethodManipulator())->makeVoidReturn($builder);

        $returnType = $builder->getNode()->getReturnType();
        if (PHP_VERSION_ID >= 70100) {
            $this->assertSame((string) new Void_(), (string) $returnType);
        } else {
            $this->assertNull($returnType);
        }
    }
}
