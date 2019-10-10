<?php

namespace Mtarld\SymbokBundle\Factory;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Serializer;
use PhpParser\Comment\Doc;

class DocFactory
{
    public function createFromDocBlock(DocBlock $docBlock): Doc
    {
        $comment = (new Serializer())->getDocComment($docBlock);

        return new Doc($comment);
    }
}
