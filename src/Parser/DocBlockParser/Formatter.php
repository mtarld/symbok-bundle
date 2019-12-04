<?php

namespace Mtarld\SymbokBundle\Parser\DocBlockParser;

use Mtarld\SymbokBundle\Repository\AnnotationRepository;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\FqsenResolver;

class Formatter
{
    private $annotationRepository;

    public function __construct(AnnotationRepository $annotationRepository)
    {
        $this->annotationRepository = $annotationRepository;
    }

    public function formatAnnotations(DocBlock $docBlock): DocBlock
    {
        return new DocBlock(
            $docBlock->getSummary() ?? '',
            $docBlock->getDescription(),
            $this->getResolvedTags($docBlock),
            $docBlock->getContext()
        );
    }

    private function getResolvedTags(DocBlock $docBlock): array
    {
        return array_map(function (BaseTag $tag) use ($docBlock) {
            if (!$tag instanceof Generic) {
                return $tag;
            }

            $resolvedTag = (new FqsenResolver())->resolve($tag->getName(), $docBlock->getContext());
            $namespace = str_replace('\\'.$resolvedTag->getName(), '', (string) $resolvedTag);

            if (!in_array($namespace, $this->annotationRepository->findNamespaces())) {
                // ltrim \ in order to be sure that Doctrine DocParser will check ignoredAnnotations
                // @see lib/Doctrine/Common/Annotations/DocParser.php:698
                $resolvedTag = ltrim((string) $resolvedTag, '\\');
            }

            return new Generic((string) $resolvedTag, $tag->getDescription());
        }, $docBlock->getTags());
    }
}
