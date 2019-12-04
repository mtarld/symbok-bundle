<?php

namespace Mtarld\SymbokBundle\Parser\DocBlockParser;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\FqsenResolver;

class Formatter
{
    public function resolveAnnotations(DocBlock $docBlock): DocBlock
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

            return new Generic((string) $resolvedTag, $tag->getDescription());
        }, $docBlock->getTags());
    }
}
