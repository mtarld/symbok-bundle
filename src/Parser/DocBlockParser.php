<?php

namespace Mtarld\SymbokBundle\Parser;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
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

        $docBlock = $this->formatter->formatAnnotations($docBlock);
        $text = $this->docFactory->createFromDocBlock($docBlock)->getReformattedText();

        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);

        return $parser->parse($text);
    }

    private function prepareContext(): void
    {
        if (false === $this->isContextReady) {
            AnnotationRegistry::registerLoader('class_exists');
            $this->isContextReady = true;
        }
    }
}
