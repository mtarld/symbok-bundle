<?php

namespace Mtarld\SymbokBundle\Service;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Mtarld\SymbokBundle\Context\ContextHolder;
use Symfony\Component\Finder\Finder;

class AnnotationService
{
    /** @var ContextHolder */
    private $contextHolder;

    public function __construct(ContextHolder $contextHolder)
    {
        $this->contextHolder = $contextHolder;
    }

    public function loadAnnotations(ComposerClassLoader $classLoader): void
    {
        $annotationFilePaths = [];
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../Annotation');
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

    public function parseAnnotations($comment): array
    {
        $comment = $this->replaceAnnotationsNamespace($comment);

        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $parser->setIgnoredAnnotationNames(['package', 'author']);
        $parser->addNamespace('Mtarld\SymbokBundle\Annotation');
        $parser->addNamespace('Doctrine\ORM\Mapping');

        return $parser->parse($comment);
    }

    private function replaceAnnotationsNamespace(string $comment): string
    {
        $ormAlias = null;
        $symbokAlias = null;

        $context = $this->contextHolder->getContext();
        foreach ($context->getNamespaceAliases() as $alias => $target) {
            if ($target == 'Doctrine\\ORM\\Mapping') {
                $ormAlias = is_int($alias) ? 'Mapping' : $alias;
            } elseif ($target == 'Mtarld\\SymbokBundle\\Annotation') {
                $symbokAlias = is_int($alias) ? 'Annotation' : $alias;
            }
        }

        $comment = str_replace('Doctrine\\ORM\\Mapping\\', '', $comment);
        if ($ormAlias) {
            $comment = str_replace("$ormAlias\\", '', $comment);
        }

        $comment = str_replace('Mtarld\\Symbok\\Annotation\\', '', $comment);
        if ($symbokAlias) {
            $comment = str_replace("$symbokAlias\\", '', $comment);
        }

        return $comment;
    }
}
