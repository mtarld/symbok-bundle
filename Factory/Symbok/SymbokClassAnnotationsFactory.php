<?php

namespace Mtarld\SymbokBundle\Factory\Symbok;

use Mtarld\SymbokBundle\Context\ContextHolder;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClassAnnotation;
use Mtarld\SymbokBundle\Service\AnnotationService;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class SymbokClassAnnotationsFactory
{
    /** @var ContextHolder */
    private $contextHolder;

    /** @var AnnotationService */
    private $annotationParserService;

    public function __construct(
        ContextHolder $contextHolder,
        AnnotationService $annotationParserService
    ) {
        $this->contextHolder = $contextHolder;
        $this->annotationParserService = $annotationParserService;
    }

    public function create($class): array
    {
        /** @var NodeClass $class */
        $parsedAnnotations = [];
        if ($class->getDocComment()) {
            $commentText = $class->getDocComment()->getText();
            foreach ($this->annotationParserService->parseAnnotations($commentText) as $annotation) {
                $parsedAnnotations[] = new SymbokClassAnnotation($annotation);
            }
        }

        return $parsedAnnotations;
    }
}
