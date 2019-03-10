<?php

namespace Mtarld\SymbokBundle\Model\Symbok;

use Mtarld\SymbokBundle\Exception\RulesNotComputed\ClassRulesNotComputedException;
use Mtarld\SymbokBundle\Model\Rules\ClassRules;
use phpDocumentor\Reflection\DocBlock;

class SymbokClass
{
    /** @var string */
    private $name;

    /** @var array */
    private $annotations = [];

    /** @var DocBlock */
    private $docBlock;

    /** @var array */
    private $properties;

    /** @var SymbokClassMethods */
    private $methods;

    /** @var ClassRules */
    private $rules;

    public function __construct(
        string $name,
        array $annotations,
        DocBlock $docBlock,
        array $properties,
        SymbokClassMethods $methods
    ) {
        $this->name = $name;
        $this->annotations = $annotations;
        $this->docBlock = $docBlock;
        $this->properties = $properties;
        $this->methods = $methods;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    public function getDocBlock(): DocBlock
    {
        return $this->docBlock;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function hasConstructor(): bool
    {
        return $this->methods->hasConstructor();
    }

    public function hasToString(): bool
    {
        return $this->methods->hasToString();
    }

    public function getRules(): ClassRules
    {
        if (!$this->rules) {
            throw new ClassRulesNotComputedException();
        }

        return $this->rules;
    }

    public function setRules(ClassRules $rules): void
    {
        $this->rules = $rules;
    }
}
