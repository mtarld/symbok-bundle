<?php

namespace Mtarld\SymbokBundle\Tests\Autoload;

use App\Entity\Product1;
use App\Entity\Product2;
use App\Model\ProductFromAnotherNamespace;
use Mtarld\SymbokBundle\Autoload\Autoloader;
use Mtarld\SymbokBundle\Autoload\AutoloadFinder;
use Mtarld\SymbokBundle\Cache\RuntimeClassCache;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group unit
 * @group autoload
 */
class AutoloaderTest extends TestCase
{
    /**
     * @dataProvider substituteClassDataProvider
     * @testdox Substitute $class and expect substituted to be $substitute
     */
    public function testSubstituteClass(string $classFqcn, bool $substitute): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $replacer = $this->createMock(ReplacerInterface::class);
        $replacer
            ->expects($this->exactly((int) $substitute))
            ->method('replace')
        ;
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturn($replacer)
        ;
        $codeFinder = $this->createMock(PhpCodeFinder::class);
        $codeFinder
            ->method('isClass')
            ->willReturn(true)
        ;

        $fileSystem = new Filesystem();
        $oldCacheFilePath = sprintf('%s/%s.php', 'var/cache/test/symbok', str_replace('\\', '/', $classFqcn));
        $fileSystem->remove($oldCacheFilePath);

        $autoloadFinder = new AutoloadFinder('Mtarld\\SymbokBundle\\Tests\\Fixtures\\App\\src\\Entity');

        $autoload = new Autoloader(
            $container,
            $logger,
            $autoloadFinder,
            new RuntimeClassCache('var/cache/test/symbok/', true),
            $this->createMock(PhpCodeParser::class),
            $codeFinder,
            ['App\\Entity']
        );
        $autoload->loadClass($classFqcn);
    }

    public function substituteClassDataProvider(): iterable
    {
        yield [Product1::class, true];
        yield [Product2::class, true];
        yield [ProductFromAnotherNamespace::class, false];
        yield ['AnotherClass', false];
        yield ['App\Entity\VirtualClass', false];
    }
}
