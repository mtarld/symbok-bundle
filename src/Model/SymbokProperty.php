<?php

namespace Mtarld\SymbokBundle\Model;

use Mtarld\SymbokBundle\Model\Relation\DoctrineRelation;
use phpDocumentor\Reflection\Type;

class SymbokProperty extends SymbokElement
{
    private $name;
    private $class;
    private $type;
    private $relation;

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
