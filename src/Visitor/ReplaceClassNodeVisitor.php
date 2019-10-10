<?php

namespace Mtarld\SymbokBundle\Visitor;

use Mtarld\SymbokBundle\Factory\DocFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;

class ReplaceClassNodeVisitor extends NodeVisitorAbstract
{
    public $class;
    private $docFactory;

    public function __construct(DocFactory $docFactory)
    {
        $this->docFactory = $docFactory;
    }

    public function enterNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }

        $node->stmts = $this->class->getStatements();
        $node->setDocComment($this->docFactory->createFromDocBlock($this->class->getDocBlock()));

        return $node;
    }
}
