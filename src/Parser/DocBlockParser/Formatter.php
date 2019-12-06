<?php

namespace Mtarld\SymbokBundle\Parser\DocBlockParser;

use Mtarld\SymbokBundle\Annotation\ToString;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\FqsenResolver;
use ReflectionClass;

class Formatter
{
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
        $symbokAnnotationNamespace = '\\'.(new ReflectionClass(ToString::class))->getNamespaceName();

        return array_map(function (BaseTag $tag) use ($docBlock, $symbokAnnotationNamespace) {
            if (!$tag instanceof Generic) {
                return $tag;
            }

            $resolvedTag = (new FqsenResolver())->resolve($tag->getName(), $docBlock->getContext());
            $namespace = str_replace('\\'.$resolvedTag->getName(), '', (string) $resolvedTag);

            if (0 === strcmp($symbokAnnotationNamespace, $namespace)) {
                // ltrim \ in order to be sure that Doctrine DocParser will check ignoredAnnotations
                // @see lib/Doctrine/Common/Annotations/DocParser.php:698
                $resolvedTag = ltrim((string) $resolvedTag, '\\');
            }

            return new Generic((string) $resolvedTag, $tag->getDescription());
        }, $docBlock->getTags());
    }
}
