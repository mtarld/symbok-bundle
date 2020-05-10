<?php

namespace Mtarld\SymbokBundle\Parser\DocBlockParser;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\FqsenResolver;

/**
 * @internal
 * @final
 */
class Formatter
{
    public function formatAnnotations(DocBlock $docBlock): DocBlock
    {
        return new DocBlock(
            $docBlock->getSummary() ?: '',
            $docBlock->getDescription(),
            $this->getResolvedTags($docBlock),
            $docBlock->getContext()
        );
    }

    /**
     * @return array<Tag>
     */
    private function getResolvedTags(DocBlock $docBlock): array
    {
        return array_map(static function (Tag $tag) use ($docBlock): Tag {
            if (!$tag instanceof Generic) {
                return $tag;
            }

            $resolvedTag = (new FqsenResolver())->resolve($tag->getName(), $docBlock->getContext());

            // ltrim \ in order to be sure that Doctrine DocParser will check ignoredAnnotations
            // @see lib/Doctrine/Common/Annotations/DocParser.php:698
            return new Generic(ltrim((string) $resolvedTag, '\\'), $tag->getDescription());
        }, $docBlock->getTags());
    }
}
