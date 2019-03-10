<?php

namespace Mtarld\SymbokBundle\Factory\Symbok;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Mtarld\SymbokBundle\Context\ContextHolder;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyAnnotation;
use Mtarld\SymbokBundle\Service\AnnotationService;
use PhpParser\Node\Stmt\Property as ClassProperty;

class SymbokPropertyAnnotationsFactory
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

    public function create($property): array
    {
        /** @var ClassProperty $property */
        $parsedAnnotations = [];
        if ($property->getDocComment()) {
            $commentText = $property->getDocComment()->getText();
            foreach ($this->annotationParserService->parseAnnotations($commentText) as $annotation) {
                $parsedAnnotations[] = new SymbokPropertyAnnotation(
                    $annotation,
                    $this->getAnnotationType($annotation)
                );
            }
        }

        return [
            'all'      => $parsedAnnotations,
            'column'   => $this->getDoctrineColumnAnnotation($parsedAnnotations),
            'relation' => $this->getDoctrineRelationAnnotation($parsedAnnotations)
        ];
    }

    private function getAnnotationType($annotation): string
    {
        if ($this->isDoctrineCollectionAnnotation($annotation)) {
            return SymbokPropertyAnnotation::TYPE_DOCTRINE_COLLECTION;
        }

        if ($this->isDoctrineEntityAnnotation($annotation)) {
            return SymbokPropertyAnnotation::TYPE_DOCTRINE_ENTITY;
        }

        if ($this->isDoctrineColumnAnnotation($annotation)) {
            return SymbokPropertyAnnotation::TYPE_DOCTRINE_COLUMN;
        }

        return SymbokPropertyAnnotation::TYPE_STANDARD;
    }

    private function isDoctrineCollectionAnnotation($annotation): bool
    {
        $class = get_class($annotation);

        return $class == OneToMany::class || $class == ManyToMany::class;
    }

    private function isDoctrineEntityAnnotation($annotation): bool
    {
        $class = get_class($annotation);

        return $class == OneToOne::class || $class == ManyToOne::class;
    }

    private function isDoctrineColumnAnnotation($annotation): bool
    {
        $class = get_class($annotation);

        return $class == Column::class;
    }

    private function getDoctrineColumnAnnotation(array $annotations): ?SymbokPropertyAnnotation
    {
        foreach ($annotations as $annotation) {
            /** @var SymbokPropertyAnnotation $annotation */
            if ($annotation->isDoctrineColumnAnnotation()) {
                return $annotation;
            }
        }

        return null;
    }

    private function getDoctrineRelationAnnotation(array $annotations): ?SymbokPropertyAnnotation
    {
        foreach ($annotations as $annotation) {
            /** @var SymbokPropertyAnnotation $annotation */
            if ($annotation->isDoctrineRelationAnnotation()) {
                return $annotation;
            }
        }

        return null;
    }
}
