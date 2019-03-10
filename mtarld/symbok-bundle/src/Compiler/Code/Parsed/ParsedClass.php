<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parsed;

use Mtarld\SymbokBundle\Compiler\Code\Parsed\Class_\ClassMethods;
use phpDocumentor\Reflection\DocBlock;

class ParsedClass
{
    /** @var string */
    private $name;

    /** @var array */
    private $annotations = [];

    /** @var DocBlock */
    private $docBlock;

    /** @var array */
    private $properties;

    /** @var ClassMethods */
    private $methods;

    public function __construct(
        string $name,
        array $annotations,
        DocBlock $docBlock,
        array $properties,
        ClassMethods $methods
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
}
