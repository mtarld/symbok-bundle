<?php

namespace Mtarld\SymbokBundle\Factory;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory as Factory;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node;

class DocBlockFactory
{
    public function createFor(Node $subject, Context $context): DocBlock
    {
        if (null === $subject->getDocComment()) {
            return new DocBlock();
        }

        return Factory::createInstance()->create(
            (string) $subject->getDocComment()->getReformattedText(),
            $context
        );
    }
}
