<?php

namespace Mtarld\SymbokBundle\Compiler\Compiler;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Mtarld\SymbokBundle\Compiler\Helper\ContextBuilder;
use Mtarld\SymbokBundle\Compiler\Helper\NodeFinder;
use Mtarld\SymbokBundle\Exception\SymbokException;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;

class FileCompiler
{
    /** @var Parser */
    private $phpParser;

    /** @var ClassCompiler */
    private $classCompiler;

    public function __construct(ComposerClassLoader $classLoader)
    {
        $this->phpParser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $this->classCompiler = new ClassCompiler();
        $this->loadAnnotations($classLoader);
    }

    private function loadAnnotations(ComposerClassLoader $classLoader): void
    {
        $annotationFilePaths = [];
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../../Annotation');
        foreach ($finder as $file) {
            $annotationFilePaths[] = $file->getRealPath();
        }
        $doctrineAnnotations = [
            OneToOne::class,
            OneToMany::class,
            ManyToOne::class,
            ManyToMany::class,
            Column::class,
            JoinColumn::class
        ];
        foreach ($doctrineAnnotations as $annotation) {
            $annotationFilePaths[] = $classLoader->findFile($annotation);
        }

        foreach ($annotationFilePaths as $filePath) {
            AnnotationRegistry::registerFile($filePath);
        }
    }

    public function compile($filename): array
    {
        try {
            $nodes = $this->phpParser->parse(file_get_contents($filename));

            $namespace = NodeFinder::findNamespace(...$nodes);
            $uses = NodeFinder::findUses(...$namespace->stmts);
            $class = NodeFinder::findClass(...$namespace->stmts);
            $context = ContextBuilder::build((string)$namespace->name, $uses);

            $this->classCompiler->compile($class, $context);

            return $nodes;
        } catch (\Exception $e) {
            throw new SymbokException("Error while compiling $filename: " . $e->getMessage());
        }
    }

}
