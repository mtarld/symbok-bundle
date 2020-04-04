<?php

namespace Mtarld\SymbokBundle\Parser;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Mtarld\SymbokBundle\Factory\DocFactory;
use Mtarld\SymbokBundle\Parser\DocBlockParser\Formatter;
use phpDocumentor\Reflection\DocBlock;

class DocBlockParser
{
    /** @var Formatter */
    private $formatter;

    /** @var DocFactory */
    private $docFactory;

    /** @var bool */
    private $isContextReady = false;

    public function __construct(
        Formatter $formatter,
        DocFactory $docFactory
    ) {
        $this->formatter = $formatter;
        $this->docFactory = $docFactory;
    }

    /**
     * @return array<mixed>
     */
    public function parseAnnotations(DocBlock $docBlock): array
    {
        $this->prepareContext();

        $docBlock = $this->formatter->formatAnnotations($docBlock);
        $text = $this->docFactory->createFromDocBlock($docBlock)->getReformattedText();

        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);

        return $parser->parse($text);
    }

    /**
     * @psalm-suppress DeprecatedMethod
     */
    private function prepareContext(): void
    {
        if (false === $this->isContextReady) {
            AnnotationRegistry::registerLoader('class_exists');
            $this->isContextReady = true;
        }
    }
}
