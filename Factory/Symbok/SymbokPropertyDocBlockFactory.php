<?php

namespace Mtarld\SymbokBundle\Factory\Symbok;

use Mtarld\SymbokBundle\Context\ContextHolder;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Node\Stmt\Property as ClassProperty;

class SymbokPropertyDocBlockFactory
{
    /** @var ContextHolder */
    private $contextHolder;

    public function __construct(ContextHolder $contextHolder)
    {
        $this->contextHolder = $contextHolder;
    }

    public function create(ClassProperty $property): ?DocBlock
    {
        $context = $this->contextHolder->getContext();

        $comment = $property->getDocComment();
        if ($comment) {
            return DocBlockFactory::createInstance()->create(
                (string)$property->getDocComment(),
                $context
            );
        }

        return null;
    }
}
