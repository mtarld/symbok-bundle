<?php

namespace Mtarld\SymbokBundle\Service;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Mtarld\SymbokBundle\Compiler\ClassCompiler;
use Mtarld\SymbokBundle\Context\ContextHolder;
use Mtarld\SymbokBundle\Exception\SymbokException;
use Mtarld\SymbokBundle\Helper\NodesFinder;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class CompilerService
{
    /** @var Parser */
    private $phpParser;

    /** @var ContextHolder */
    private $contextHolder;

    /** @var ClassCompiler */
    private $classCompiler;

    /** @var ComposerClassLoader */
    private $classLoader;

    /** @var AnnotationService */
    private $annotationService;

    public function __construct(
        ContextHolder $contextHolder,
        AnnotationService $annotationService,
        ClassCompiler $classCompiler
    ) {
        $this->classCompiler = $classCompiler;
        $this->annotationService = $annotationService;
        $this->contextHolder = $contextHolder;

        $this->phpParser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
    }

    public function setClassLoader(ComposerClassLoader $classLoader): void
    {
        $this->classLoader = $classLoader;
    }

    public function compile($filename): Stmt
    {
        try {
            $stmts = $this->phpParser->parse(file_get_contents($filename));

            $namespace = NodesFinder::findNamespace(...$stmts);
            $uses = NodesFinder::findUses(...$namespace->stmts);
            $class = NodesFinder::findClass(...$namespace->stmts);

            $this->contextHolder->buildContext((string)$namespace->name, $uses);
            $this->annotationService->loadAnnotations($this->classLoader);

            $this->classCompiler->compile($class);

            return $stmts[0];
        } catch (\Exception $e) {
            throw new SymbokException("Error while compiling $filename: " . $e->getMessage());
        }
    }
}
