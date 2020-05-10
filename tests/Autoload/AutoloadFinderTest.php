<?php

namespace {
    $mockSplAutoloadFunctions = null;
}

namespace Mtarld\SymbokBundle\Autoload {
    /**
     * @return array|bool
     */
    function spl_autoload_functions()
    {
        global $mockSplAutoloadFunctions;

        if (null === $mockSplAutoloadFunctions) {
            return \spl_autoload_functions();
        }

        return ($mockSplAutoloadFunctions)();
    }
}

namespace Mtarld\SymbokBundle\Tests\Autoload {
    use Composer\Autoload\ClassLoader;
    use Mtarld\SymbokBundle\Autoload\AutoloadFinder;
    use PHPUnit\Framework\TestCase;
    use ReflectionClass;
    use RuntimeException;
    use Symfony\Component\ErrorHandler\DebugClassLoader;

    /**
     * @group unit
     * @group autoload
     */
    class AutoloadFinderTest extends TestCase
    {
        public function tearDown(): void
        {
            global $mockSplAutoloadFunctions;
            $mockSplAutoloadFunctions = null;
        }

        public function testClassLoaderNotMatched(): void
        {
            $classLoader = $this->createMock(ClassLoader::class);
            $classLoader
                ->method('getPrefixesPsr4')
                ->willReturn(['foo' => 'foo'])
            ;

            $autoloadFinder = new AutoloadFinder('bar');

            $reflection = new ReflectionClass(get_class($autoloadFinder));
            $method = $reflection->getMethod('isMatchingClassLoader');
            $method->setAccessible(true);

            $this->assertNull($method->invokeArgs($autoloadFinder, [$classLoader]));
        }

        public function testClassLoaderMatched(): void
        {
            $classLoader = $this->createMock(ClassLoader::class);
            $classLoader
                ->method('getPrefixesPsr4')
                ->willReturn(['foo' => 'foo'])
            ;

            $autoloadFinder = new AutoloadFinder('foo');

            $reflection = new ReflectionClass(get_class($autoloadFinder));
            $method = $reflection->getMethod('isMatchingClassLoader');
            $method->setAccessible(true);

            $this->assertSame($classLoader, $method->invokeArgs($autoloadFinder, [$classLoader]));
        }

        public function testExtractComposerClassLoader(): void
        {
            $autoloadFinder = new AutoloadFinder('foo');

            $reflection = new ReflectionClass(get_class($autoloadFinder));
            $method = $reflection->getMethod('extractComposerClassLoader');
            $method->setAccessible(true);

            $classLoader = $this->createMock(ClassLoader::class);
            $this->assertNull($method->invokeArgs($autoloadFinder, [null]));

            $debugClassLoader = new DebugClassLoader([$classLoader, 'findFile']);

            $emptyDebugClassLoader = $this->createMock(DebugClassLoader::class);
            $emptyDebugClassLoader
                ->method('getClassLoader')
                ->willReturn(function () {})
            ;

            $this->assertSame($classLoader, $method->invokeArgs($autoloadFinder, [$classLoader]));
            $this->assertSame($classLoader, $method->invokeArgs($autoloadFinder, [$debugClassLoader]));
            $this->assertNull($method->invokeArgs($autoloadFinder, [$emptyDebugClassLoader]));
        }

        public function testFindComposerClassLoader(): void
        {
            global $mockSplAutoloadFunctions;

            $autoloadFinder = new AutoloadFinder('foo');

            $reflection = new ReflectionClass(get_class($autoloadFinder));
            $method = $reflection->getMethod('findComposerClassLoader');
            $method->setAccessible(true);

            $mockSplAutoloadFunctions = static function (): bool {
                return false;
            };

            $this->assertNull($method->invokeArgs($autoloadFinder, []));

            $mockSplAutoloadFunctions = static function (): array {
                return [null];
            };
            $this->assertNull($method->invokeArgs($autoloadFinder, []));

            $mockSplAutoloadFunctions = static function (): array {
                return [[null]];
            };
            $this->assertNull($method->invokeArgs($autoloadFinder, []));

            $classLoader = $this->createMock(ClassLoader::class);
            $classLoader
                ->method('getPrefixesPsr4')
                ->willReturn(['foo' => 'foo'])
            ;

            $debugClassLoader = new DebugClassLoader([$classLoader, 'findFile']);
            $mockSplAutoloadFunctions = static function () use ($debugClassLoader): array {
                return [[$debugClassLoader]];
            };

            $this->assertSame($classLoader, $method->invokeArgs($autoloadFinder, []));
        }

        public function testGetClassLoader(): void
        {
            global $mockSplAutoloadFunctions;

            $autoloadFinder = new AutoloadFinder('foo');

            $reflection = new ReflectionClass(get_class($autoloadFinder));
            $method = $reflection->getMethod('getClassLoader');
            $method->setAccessible(true);

            $classLoader = $this->createMock(ClassLoader::class);
            $classLoader
                ->method('getPrefixesPsr4')
                ->willReturn(['foo' => 'foo'])
            ;

            $debugClassLoader = new DebugClassLoader([$classLoader, 'findFile']);
            $mockSplAutoloadFunctions = static function () use ($debugClassLoader): array {
                return [[$debugClassLoader]];
            };

            $this->assertSame($classLoader, $method->invokeArgs($autoloadFinder, []));

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage("Could not find a Composer autoloader that autoloads from 'foo\'");

            $mockSplAutoloadFunctions = static function (): bool {
                return false;
            };
            $autoloadFinder = new AutoloadFinder('foo');
            $method->invokeArgs($autoloadFinder, []);
        }

        public function testFindFile(): void
        {
            $classLoader = $this->createMock(ClassLoader::class);
            $classLoader
                ->method('getPrefixesPsr4')
                ->willReturn(['foo' => 'foo'])
            ;
            $classLoader
                ->method('findFile')
                ->willReturn('bar')
            ;

            $debugClassLoader = new DebugClassLoader([$classLoader, 'findFile']);

            global $mockSplAutoloadFunctions;
            $mockSplAutoloadFunctions = static function () use ($debugClassLoader): array {
                return [[$debugClassLoader]];
            };

            $this->assertSame('bar', (new AutoloadFinder('foo'))->findFile(''));

            $classLoader = $this->createMock(ClassLoader::class);
            $classLoader
                ->method('getPrefixesPsr4')
                ->willReturn(['foo' => 'foo'])
            ;
            $classLoader
                ->method('findFile')
                ->willReturn(null)
            ;
            $debugClassLoader = new DebugClassLoader([$classLoader, 'findFile']);
            $mockSplAutoloadFunctions = static function () use ($debugClassLoader): array {
                return [[$debugClassLoader]];
            };

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage("Cannot find file related to class 'foo'");
            (new AutoloadFinder('foo'))->findFile('foo');
        }
    }
}
