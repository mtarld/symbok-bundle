<?php

namespace Mtarld\SymbokBundle\Model;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Context;

class SymbokClass extends SymbokElement
{
    private $name;
    private $statements;
    private $context;
    private $docBlock;
    private $properties;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatements(): array
    {
        return $this->statements;
    }

    public function setStatements(array $statements): self
    {
        $this->statements = $statements;

        return $this;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function setContext(Context $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getDocBlock(): DocBlock
    {
        return $this->docBlock;
    }

    public function setDocBlock(?DocBlock $docBlock): self
    {
        $this->docBlock = $docBlock;

        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function setAnnotations(array $annotations): self
    {
        $this->annotations = $annotations;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
