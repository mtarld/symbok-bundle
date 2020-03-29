<?php

namespace Mtarld\SymbokBundle\Model;

use Mtarld\SymbokBundle\Annotation\AnnotationInterface;
use Mtarld\SymbokBundle\Model\Relation\DoctrineRelation;
use phpDocumentor\Reflection\Type;

class SymbokProperty extends SymbokElement
{
    /** @var string */
    private $name;

    /** @var SymbokClass */
    private $class;

    /** @var Type|null */
    private $type;

    /** @var DoctrineRelation|null */
    private $relation;

    public function __construct(
        string $name,
        SymbokClass $class,
        ?Type $type,
        ?DoctrineRelation $relation,
        array $annotations
    ) {
        $this->name = $name;
        $this->class = $class;
        $this->type = $type;
        $this->relation = $relation;

        parent::__construct($annotations);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getClass(): SymbokClass
    {
        return $this->class;
    }

    public function setClass(SymbokClass $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

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

    public function setRelation(?DoctrineRelation $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    public function getRelation(): ?DoctrineRelation
    {
        return $this->relation;
    }
}
