<?php

namespace Mtarld\SymbokBundle\Factory\Symbok;

use Mtarld\SymbokBundle\Context\ContextHolder;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class SymbokClassDocBlockFactory
{
    /** @var ContextHolder */
    private $contextHolder;

    public function __construct(ContextHolder $contextHolder)
    {
        $this->contextHolder = $contextHolder;
    }

    public function create(NodeClass $class): DocBlock
    {
        $context = $this->contextHolder->getContext();
        if (empty((string)$class->getDocComment())) {
            return new DocBlock('', null, [], $context);
        }

        $comment = $class->getDocComment() !== null ? (string)$class->getDocComment()->getReformattedText() : null;

        return DocBlockFactory::createInstance()->create($comment, $context);
    }
}
