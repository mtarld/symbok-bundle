<?php

namespace Mtarld\SymbokBundle\Parser;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Mtarld\SymbokBundle\Autoload\Autoload;
use Mtarld\SymbokBundle\Factory\DocFactory;
use Mtarld\SymbokBundle\Parser\DocBlockParser\Formatter;
use Mtarld\SymbokBundle\Repository\AnnotationRepository;
use phpDocumentor\Reflection\DocBlock;

class DocBlockParser
{
    private $formatter;
    private $docFactory;
    private $annotationRepository;

    private $isContextReady = false;

    public function __construct(
        Formatter $formatter,
        DocFactory $docFactory,
        AnnotationRepository $annotationRepository
    ) {
        $this->formatter = $formatter;
        $this->docFactory = $docFactory;
        $this->annotationRepository = $annotationRepository;
    }

    public function parseAnnotations(DocBlock $docBlock): array
    {
        $this->prepareContext();

        $docBlock = $this->formatter->resolveAnnotations($docBlock);
        $text = $this->docFactory->createFromDocBlock($docBlock)->getReformattedText();

        return $this->getParser()->parse($text);
    }

    private function getParser(): DocParser
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);

        foreach ($this->annotationRepository->findNamespaces() as $namespace) {
            $parser->addNamespace($namespace);
        }

        return $parser;
    }

    public function prepareContext(): void
    {
        if (!$this->isContextReady) {
            $annotationFilePaths = array_map(function (string $annotation) {
                return Autoload::getClassLoader()->findFile($annotation);
            }, $this->annotationRepository->findAll());

            array_walk($annotationFilePaths, [AnnotationRegistry::class, 'registerFile']);

            $this->isContextReady = true;
        }
    }
}
