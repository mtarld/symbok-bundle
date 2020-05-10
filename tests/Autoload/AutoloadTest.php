<?php

namespace Mtarld\SymbokBundle\Tests\Autoload;

use Mtarld\SymbokBundle\Autoload\Autoload;
use Mtarld\SymbokBundle\Autoload\AutoloadFinder;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity\Product1;
use Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity\Product2;
use Mtarld\SymbokBundle\Tests\Fixtures\App\src\Model\ProductFromAnotherNamespace;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group unit
 * @group autoload
 */
class AutoloadTest extends TestCase
{
    /**
     * @dataProvider substituteClassDataProvider
     * @testdox Substitute $class and expect substituted to be $substitute
     */
    public function testSubstituteClass(string $class, bool $substitute): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $replacer = $this->createMock(ReplacerInterface::class);
        $replacer
            ->expects($this->exactly((int) $substitute))
            ->method('replace')
        ;

        $fileSystem = new Filesystem();
        $oldCacheFilePath = sprintf('var/cache/%s.php', $class);
        $fileSystem->remove($oldCacheFilePath);

        $autoloadFinder = new AutoloadFinder('Mtarld\\SymbokBundle\\Tests\\Fixtures\\App\\src\\Entity');

        $autoload = new Autoload(
            $replacer,
            $logger,
            $autoloadFinder,
            ['Mtarld\\SymbokBundle\\Tests\\Fixtures\\App\\src\\Entity'],
            'var/cache/',
            true
        );
        $autoload->loadClass($class);
    }

    public function substituteClassDataProvider(): iterable
    {
        yield [Product1::class, true];
        yield [Product2::class, true];
        yield [ProductFromAnotherNamespace::class, false];
        yield ['AnotherClass', false];
        yield ['Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity\VirtualClass', false];
    }
}
