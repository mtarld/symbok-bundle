<?php

namespace Mtarld\SymbokBundle\Serializer;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Serializer;

/**
 * @internal
 * @final
 */
class DocBlockSerializer
{
    /** @var Serializer */
    private $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer(0, ' ', true, null, new DocBlockTagSerializer());
    }

    public function getDocComment(DocBlock $docblock): string
    {
        return $this->serializer->getDocComment($docblock);
    }
}
