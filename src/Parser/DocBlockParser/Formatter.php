<?php

namespace Mtarld\SymbokBundle\Parser\DocBlockParser;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context;

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
        return array_map(function (Tag $tag) use ($docBlock): Tag {
            return $this->getResolvedTag($tag, $docBlock->getContext());
        }, $docBlock->getTags());
    }

    private function getResolvedTag(Tag $tag, ?Context $context = null): Tag
    {
        if (!$tag instanceof Generic) {
            return $tag;
        }

        if (($description = $tag->getDescription()) instanceof Description) {
            $resolvedDescriptionTags = [];
            foreach ($description->getTags() as $descriptionTag) {
                $resolvedDescriptionTags[] = $this->getResolvedTag($descriptionTag, $context);
            }

            $description = new Description($description->getBodyTemplate(), $resolvedDescriptionTags);
        }

        return new Generic($this->resolveTag($tag, $context), $description);
    }

    private function resolveTag(Tag $tag, ?Context $context = null): string
    {
        // ltrim \ in order to be sure that Doctrine DocParser will check ignoredAnnotations
        // @see lib/Doctrine/Common/Annotations/DocParser.php:698
        return ltrim((string) (new FqsenResolver())->resolve($tag->getName(), $context), '\\');
    }
}
