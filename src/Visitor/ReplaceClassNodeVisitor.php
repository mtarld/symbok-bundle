<?php

namespace Mtarld\SymbokBundle\Visitor;

use Mtarld\SymbokBundle\Factory\DocFactory;
use Mtarld\SymbokBundle\Model\SymbokClass;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ReplaceClassNodeVisitor extends NodeVisitorAbstract
{
    /** @var SymbokClass */
    public $class;

    /** @var DocFactory */
    private $docFactory;

    public function __construct(DocFactory $docFactory)
    {
        $this->docFactory = $docFactory;
    }

    public function enterNode(Node $node): ?Class_
    {
        if (!$node instanceof Class_) {
            return null;
        }

        $node->stmts = $this->class->getStatements();
        $node->setDocComment($this->docFactory->createFromDocBlock($this->class->getDocBlock()));

        return $node;
    }
}
