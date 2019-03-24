<?php

namespace Mtarld\SymbokBundle\Model;

use PhpParser\Node\Stmt;

class Statements implements \IteratorAggregate
{
    private $statements = [];

    public function merge(Statements $other): Statements
    {
        $this->add(...$other->statements);

        return $this;
    }

    public function add(Stmt ...$stmts): void
    {
        foreach ($stmts as $stmt) {
            $this->statements[] = $stmt;
        }
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->statements);
    }
}
