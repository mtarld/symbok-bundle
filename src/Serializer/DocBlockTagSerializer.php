<?php

namespace Mtarld\SymbokBundle\Serializer;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter as TagSerializerInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;

/**
 * @internal
 * @final
 */
class DocBlockTagSerializer implements TagSerializerInterface
{
    public function format(Tag $tag): string
    {
        if (!$tag instanceof Generic) {
            return $tag->render();
        }

        $description = '';
        if (($tagDescription = $tag->getDescription()) instanceof Description) {
            $description = $tagDescription->render($this);
        }

        return trim(sprintf('@%s%s', $tag->getName(), $description));
    }
}
