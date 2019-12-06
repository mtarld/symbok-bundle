<?php

namespace Mtarld\SymbokBundle\Model\Relation;

use Mtarld\SymbokBundle\Util\MethodNameGenerator;

abstract class DoctrineRelation
{
    protected $className;
    protected $targetClassName;
    protected $targetPropertyName;
    protected $isOwning;

    abstract public function getTargetSetterMethodName(): string;

    public function getTargetClassName(): string
    {
        return $this->targetClassName;
    }

    public function setTargetClassName(string $targetClassName)
    {
        $this->targetClassName = $targetClassName;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className)
    {
        $this->className = $className;

        return $this;
    }

    public function getTargetPropertyName(): ?string
    {
        return $this->targetPropertyName;
    }

    public function setTargetPropertyName(?string $targetPropertyName)
    {
        $this->targetPropertyName = $targetPropertyName;

        return $this;
    }

    public function isOwning(): bool
    {
        return $this->isOwning;
    }

    public function setIsOwning(bool $isOwning)
    {
        $this->isOwning = $isOwning;

        return $this;
    }

    public function getTargetGetterMethodName(): string
    {
        return MethodNameGenerator::generate($this->getMethodTargetName(), MethodNameGenerator::METHOD_GET);
    }

    protected function getMethodTargetName(): string
    {
        return !empty($this->getTargetPropertyName())
                       ? $this->getTargetPropertyName()
                       : $this->getClassName()
        ;
    }
}
