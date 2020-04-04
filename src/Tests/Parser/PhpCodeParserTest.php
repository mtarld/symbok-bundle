<?php

namespace {
    $mockFileGetContents = null;
}

namespace Mtarld\SymbokBundle\Parser {
    /**
     * @return false|string
     */
    function file_get_contents()
    {
        global $mockFileGetContents;

        if (null === $mockFileGetContents) {
            return \file_get_contents(...func_get_args());
        }

        return ($mockFileGetContents)();
    }
}

namespace Mtarld\SymbokBundle\Tests\Parser {
    use Mtarld\SymbokBundle\Autoload\AutoloadFinder;
    use Mtarld\SymbokBundle\Exception\IOException;
    use Mtarld\SymbokBundle\Parser\PhpCodeParser;
    use Mtarld\SymbokBundle\Tests\Fixtures\files\Product1;
    use PhpParser\Node\Stmt\Namespace_;
    use PHPUnit\Framework\TestCase;

    /**
     * @group unit
     * @group parser
     */
    class PhpCodeParserTest extends TestCase
    {
        protected function tearDown(): void
        {
            global $mockFileGetContents;
            $mockFileGetContents = null;
        }

        public function testParseStatements(): void
        {
            $autoloaderFinder = new AutoloadFinder('Mtarld\\SymbokBundle\\Tests\\Fixtures\\Files');

            $statements = (new PhpCodeParser($autoloaderFinder))->parseStatements(Product1::class);

            $this->assertInstanceOf(Namespace_::class, $statements[0]);
        }

        public function testParseStatementsFromPathWrongPath(): void
        {
            $autoloaderFinder = new AutoloadFinder('Mtarld\SymbokBundle\Tests\Fixtures\Files');

            $this->expectException(IOException::class);
            $this->expectExceptionMessage("Cannot read file 'foo'. Exception: file_get_contents(foo): failed to open stream: No such file or directory");
            (new PhpCodeParser($autoloaderFinder))->parseStatementsFromPath('foo');
        }

        public function testParseStatementsFromPathCannotRead(): void
        {
            $autoloaderFinder = new AutoloadFinder('Mtarld\SymbokBundle\Tests\Fixtures\Files');

            global $mockFileGetContents;
            $mockFileGetContents = static function (): bool {
                return false;
            };

            $this->expectException(IOException::class);
            $this->expectExceptionMessage("Cannot read file 'foo'");
            (new PhpCodeParser($autoloaderFinder))->parseStatementsFromPath('foo');
        }
    }
}
