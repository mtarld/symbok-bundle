<?php

namespace Mtarld\SymbokBundle\Model;

use Mtarld\SymbokBundle\Annotation\AnnotationInterface;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt;

class SymbokClass extends SymbokElement
{
    /** @var string */
    private $name;

    /** @var array<Stmt> */
    private $statements;

    /** @var DocBlock */
    private $docBlock;

    /** @var array<SymbokProperty> */
    private $properties;

    /** @var Context */
    private $context;

    public function __construct(
        string $name,
        array $statements,
        DocBlock $docBlock,
        array $properties,
        array $annotations,
        Context $context
    ) {
        $this->name = $name;
        $this->statements = $statements;
        $this->docBlock = $docBlock;
        $this->properties = $properties;
        $this->context = $context;

        parent::__construct($annotations);
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<Stmt>
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    /**
     * @param array<Stmt> $statements
     */
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

    public function setDocBlock(DocBlock $docBlock): self
    {
        $this->docBlock = $docBlock;

        return $this;
    }

    /**
     * @return array<SymbokProperty>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array<SymbokProperty> $properties
     */
    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @param array<AnnotationInterface> $annotations
     */
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
