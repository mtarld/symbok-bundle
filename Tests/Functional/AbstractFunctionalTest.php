<?php

namespace Mtarld\SymbokBundle\Tests\Functional;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Mtarld\SymbokBundle\Context\ContextHolder;
use Mtarld\SymbokBundle\Helper\NodesFinder;
use Mtarld\SymbokBundle\Service\AnnotationService;
use PhpParser\Node\Stmt\Class_ as NodeClass;
use PhpParser\ParserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Debug\DebugClassLoader;

abstract class AbstractFunctionalTest extends KernelTestCase
{
    /** @var NodeClass */
    protected $nodeClass;

    public function setUp()
    {
        static::bootKernel();
    }

    protected function buildContext(string $filePath): void
    {
        $phpParser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $nodes = $phpParser->parse(file_get_contents($filePath));

        $namespace = NodesFinder::findNamespace(...$nodes);
        $uses = NodesFinder::findUses(...$namespace->stmts);
        $this->nodeClass = NodesFinder::findClass(...$namespace->stmts);

        $contextHolder = self::$container->get(ContextHolder::class);
        $contextHolder->buildContext((string)$namespace->name, $uses);
    }

    protected function loadAnnotations(): void
    {
        $classLoader = $this->getClassLoader();

        /** @var AnnotationService $annotationService */
        $annotationService = self::$container->get(AnnotationService::class);
        $annotationService->loadAnnotations($classLoader);
    }

    protected function getClassLoader(): ComposerClassLoader
    {
        $classLoader = null;
        foreach (spl_autoload_functions() as $loader) {
            if (is_array($loader)) {
                if (is_a($loader[0], DebugClassLoader::class)) {
                    $loader = $loader[0]->getClassLoader();
                }
            } else {
                continue;
            }

            if (is_a($loader[0], ComposerClassLoader::class) && method_exists($loader[0], 'findFile')) {
                $classLoader = $loader[0];
            }
        }

        if (!$classLoader) {
            throw new \RuntimeException('Unable to find ' . ComposerClassLoader::class);
        }

        return $classLoader;
    }
}
