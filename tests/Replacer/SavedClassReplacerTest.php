<?php

namespace {
    $mockFopen = null;
    $mockFstat = null;
    $mockFtell = null;
}

namespace Mtarld\SymbokBundle\Replacer {
    /**
     * @return false|resource
     */
    function fopen()
    {
        global $mockFopen;
        if (null === $mockFopen) {
            return \fopen(...func_get_args());
        }

        return ($mockFopen)();
    }
}

namespace Mtarld\SymbokBundle\Tests\Replacer {
    use Mtarld\SymbokBundle\Autoload\AutoloadFinder;
    use Mtarld\SymbokBundle\Compiler\SavedClassCompiler;
    use Mtarld\SymbokBundle\Exception\IOException;
    use Mtarld\SymbokBundle\Factory\DocFactory;
    use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
    use Mtarld\SymbokBundle\Parser\PhpCodeParser;
    use Mtarld\SymbokBundle\Replacer\SavedClassReplacer;
    use PhpParser\Node\Stmt\Class_;
    use PHPUnit\Framework\TestCase;
    use ReflectionClass;
    use RuntimeException;

    /**
     * @group unit
     * @group replacer
     */
    class SavedClassReplacerTest extends TestCase
    {
        protected function tearDown(): void
        {
            global $mockFopen;
            global $mockFstat;
            global $mockFtell;

            $mockFopen = null;
            $mockFstat = null;
            $mockFtell = null;
        }

        /**
         * @testdox Find original class position in file
         */
        public function testGetOriginalClassFilePos(): void
        {
            $replacer = new SavedClassReplacer(
                $this->createMock(SavedClassCompiler::class),
                $this->createMock(DocFactory::class),
                $this->createMock(PhpCodeFinder::class),
                $this->createMock(PhpCodeParser::class),
                $this->createMock(AutoloadFinder::class)
            );

            $reflection = new ReflectionClass(get_class($replacer));
            $method = $reflection->getMethod('getOriginalClassFilePos');
            $method->setAccessible(true);

            $class = new Class_('a');

            $this->expectException(RuntimeException::class);
            $method->invokeArgs($replacer, [$class, fopen('.', 'rb')]);
        }

        /**
         * @testdox Cannot open class file
         */
        public function testCannotOpenClassFile(): void
        {
            $autoloadFinder = $this->createMock(AutoloadFinder::class);
            $autoloadFinder
                ->method('findClassPath')
                ->willReturn('foo')
            ;

            global $mockFopen;
            $mockFopen = static function (): bool {
                return false;
            };

            $replacer = new SavedClassReplacer(
                $this->createMock(SavedClassCompiler::class),
                $this->createMock(DocFactory::class),
                $this->createMock(PhpCodeFinder::class),
                $this->createMock(PhpCodeParser::class),
                $autoloadFinder
            );

            $this->expectException(IOException::class);
            $this->expectExceptionMessage("Cannot open file 'foo'");
            $replacer->replace('foo');
        }
    }
}
