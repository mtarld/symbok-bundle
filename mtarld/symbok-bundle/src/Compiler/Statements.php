<?php

namespace Mtarld\SymbokBundle\Compiler;

use PhpParser\Node\Stmt;

class Statements implements \IteratorAggregate, \Countable
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

    public function count()
    {
        return count($this->statements);
    }
}
