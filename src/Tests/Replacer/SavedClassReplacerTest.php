<?php

namespace Mtarld\SymbokBundle\Tests\Replacer;

use Mtarld\SymbokBundle\Compiler\SavedClassCompiler;
use Mtarld\SymbokBundle\Exception\RuntimeException;
use Mtarld\SymbokBundle\Factory\DocFactory;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Replacer\SavedClassReplacer;
use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group unit
 * @group replacer
 */
class SavedClassReplacerTest extends TestCase
{
    /**
     * @testdox Find original class position in file
     */
    public function testGetOriginalClassFilePos()
    {
        $replacer = new SavedClassReplacer(
            $this->createMock(SavedClassCompiler::class),
            $this->createMock(DocFactory::class),
            $this->createMock(PhpCodeFinder::class),
            $this->createMock(PhpCodeParser::class)
        );

        $reflection = new ReflectionClass(get_class($replacer));
        $method = $reflection->getMethod('getOriginalClassFilePos');
        $method->setAccessible(true);

        $class = new Class_('a');

        $this->expectException(RuntimeException::class);
        $method->invokeArgs($replacer, [$class, fopen('.', 'r')]);
    }
}
