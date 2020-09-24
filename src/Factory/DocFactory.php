<?php

namespace Mtarld\SymbokBundle\Factory;

use Mtarld\SymbokBundle\Serializer\DocBlockSerializer;
use phpDocumentor\Reflection\DocBlock;
use PhpParser\Comment\Doc;

/**
 * @internal
 * @final
 */
class DocFactory
{
    /** @var DocBlockSerializer */
    private $docBlockSerializer;

    public function __construct(DocBlockSerializer $docBlockSerializer)
    {
        $this->docBlockSerializer = $docBlockSerializer;
    }

    public function createFromDocBlock(DocBlock $docBlock): Doc
    {
        return new Doc($this->docBlockSerializer->getDocComment($docBlock));
    }
}
