<?php

namespace Mtarld\SymbokBundle\Tests\Parser;

use Mtarld\SymbokBundle\Exception\RuntimeException;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Tests\Fixtures\files\Product1;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group parser
 */
class PhpCodeParserTest extends TestCase
{
    public function testParseStatements(): void
    {
        $statements = (new PhpCodeParser())->parseStatements(Product1::class);

        $this->assertIsArray($statements);
        $this->assertInstanceOf(Namespace_::class, $statements[0]);
        $this->assertIsArray($statements[0]->stmts);
        foreach ($statements[0]->stmts as $statement) {
            $this->assertInstanceOf(Stmt::class, $statement);
        }
    }

    public function testWrongPath(): void
    {
        $this->expectException(RuntimeException::class);
        (new PhpCodeParser())->parseStatementsFromPath('foo');
    }
}
