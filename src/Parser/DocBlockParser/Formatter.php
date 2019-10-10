<?php

namespace Mtarld\SymbokBundle\Parser\DocBlockParser;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\FqsenResolver;

class Formatter
{
    public function cleanAnnotations(DocBlock $docBlock, array $namespaces): DocBlock
    {
        $docBlock = $this->removeOutOfNamespaceTags($docBlock, $namespaces);

        return new DocBlock(
            $docBlock->getSummary() ?? '',
            $docBlock->getDescription(),
            $this->getResolvedTags($docBlock),
            $docBlock->getContext()
        );
    }

    private function removeOutOfNamespaceTags(DocBlock $docBlock, array $namespaces): DocBlock
    {
        $tags = $docBlock->getTags();
        array_walk($tags, function (Tag $tag) use ($docBlock, $namespaces) {
            if (!$tag instanceof Generic) {
                return;
            }

            $resolvedTag = (new FqsenResolver())->resolve($tag->getName(), $docBlock->getContext());
            $namespace = str_replace('\\'.$resolvedTag->getName(), '', (string) $resolvedTag);

            if (!in_array($namespace, $namespaces)) {
                $docBlock->removeTag($tag);
            }
        });

        return $docBlock;
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
