<?php

namespace {
    $mockSplAutoloadFunctions = false;
}

namespace Mtarld\SymbokBundle\Autoload {
    function spl_autoload_functions()
    {
        global $mockSplAutoloadFunctions;
        if (true === $mockSplAutoloadFunctions) {
            return [];
        }

        return call_user_func_array('\spl_autoload_functions', func_get_args());
    }
}

namespace Mtarld\SymbokBundle\Tests\Autoload {
    use Composer\Autoload\ClassLoader;
    use Mtarld\SymbokBundle\Autoload\Autoload;
    use Mtarld\SymbokBundle\Exception\RuntimeException;
    use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
    use Mtarld\SymbokBundle\Tests\Fixtures\AnotherNamespace\ProductFromAnotherNamespace;
    use Mtarld\SymbokBundle\Tests\Fixtures\files\Product1;
    use Mtarld\SymbokBundle\Tests\Fixtures\files\Product2;
    use PHPUnit\Framework\TestCase;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\Filesystem\Filesystem;

    /**
     * @group unit
     * @group autoload
     */
    class AutoloadTest extends TestCase
    {
        public function testGetClassLoaderNotFound(): void
        {
            global $mockSplAutoloadFunctions;
            $mockSplAutoloadFunctions = true;

            $this->expectException(RuntimeException::class);

            Autoload::$classLoader = null;
            Autoload::getClassLoader();
        }

        public function testGetClassLoaderFetchedOnce(): void
        {
            global $mockSplAutoloadFunctions;
            $mockSplAutoloadFunctions = false;

            $this->assertInstanceOf(ClassLoader::class, Autoload::getClassLoader());
        }

        public function testGetClassLoaderFetchedTwiceUsesLocalCache(): void
        {
            global $mockSplAutoloadFunctions;
            $mockSplAutoloadFunctions = false;

            Autoload::getClassLoader();
            $this->assertInstanceOf(ClassLoader::class, Autoload::getClassLoader());
        }

        /**
         * @dataProvider substituteClassDataProvider
         * @testdox Substitute $class and expect substituted to be $substitute
         */
        public function testSubstituteClass(string $class, bool $substitute): void
        {
            global $mockSplAutoloadFunctions;
            $mockSplAutoloadFunctions = false;

            $replacer = $this->createMock(ReplacerInterface::class);
            $logger = $this->createMock(LoggerInterface::class);
            $replacer
                ->expects($this->exactly((int) $substitute))
                ->method('replace')
                ;

            $fileSystem = new Filesystem();
            $oldCacheFilePath = 'var'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$class.'.php';
            $fileSystem->remove($oldCacheFilePath);

            $autoload = new Autoload(
                $replacer,
                $logger,
                ['namespaces' => ['Mtarld\\SymbokBundle\\Tests\\Fixtures\\files']],
                'var'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR,
                true
            );
            $autoload->loadClass($class);
        }

        public function substituteClassDataProvider()
        {
            yield [Product1::class, true];
            yield [Product2::class, true];
            yield [ProductFromAnotherNamespace::class, false];
            yield ['AnotherClass', false];
            yield ['Mtarld\SymbokBundle\Tests\Fixtures\files\VirtualClass', false];
        }
    }
}
