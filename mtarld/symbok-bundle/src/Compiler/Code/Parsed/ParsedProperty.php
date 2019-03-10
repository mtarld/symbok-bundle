<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parsed;

use Mtarld\SymbokBundle\Annotation\Nullable;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Property\PropertyAnnotation;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Property\PropertyMethods;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Property\PropertyTypes;
use phpDocumentor\Reflection\Type;

class ParsedProperty
{
    /** @var string */
    private $name;

    /** @var string */
    private $className;

    /** @var PropertyTypes */
    private $types;

    /** @var PropertyAnnotation[] */
    private $annotations = [];

    /** @var PropertyMethods */
    private $methods;

    /** @var bool */
    private $noAdd = false;

    /** @var bool */
    private $noRemove = false;

    /** @var bool|null */
    private $nullable;

    public function __construct(
        string $name,
        string $className,
        PropertyTypes $types,
        PropertyMethods $methods,
        array $annotations
    ) {
        $this->name = $name;
        $this->className = $className;
        $this->types = $types;
        $this->annotations = $annotations;
        $this->methods = $methods;
        $this->proceedAnnotations();
    }

    private function proceedAnnotations(): void
    {
        foreach ($this->getAnnotations() as $annotation) {
            /** @var PropertyAnnotation $annotation */
            $realAnnotation = $annotation->getRealAnnotation();
            if ($realAnnotation instanceof Setter) {
                $this->noAdd = $realAnnotation->noAdd;
            }
            if ($realAnnotation instanceof Setter) {
                $this->noRemove = $realAnnotation->noRemove;
            }
            if ($realAnnotation instanceof Nullable) {
                $this->nullable = $realAnnotation->nullable;
            }
        }
    }

    public function getAnnotations(): array
    {
        return $this->annotations['all'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getType(): Type
    {
        return $this->types->getBaseType();
    }

    public function getRelationType(): ?Type
    {
        return $this->types->getRelationType();
    }

    public function getDoctrineRelationAnnotation(): ?PropertyAnnotation
    {
        return $this->annotations['relation'];
    }

    public function getDoctrineColumnAnnotation(): ?PropertyAnnotation
    {
        return $this->annotations['column'];
    }

    public function hasGetter(): bool
    {
        return $this->methods->hasGetter();
    }

    public function hasSetter(): bool
    {
        return $this->methods->hasSetter();
    }

    public function hasAdder(): bool
    {
        return $this->methods->hasAdder();
    }

    public function hasRemover(): bool
    {
        return $this->methods->hasRemover();
    }

    public function canUseAdder(): bool
    {
        return !$this->noAdd;
    }

    public function canUseRemover(): bool
    {
        return !$this->noRemove;
    }

    public function isNullable(): ?bool
    {
        return $this->nullable;
    }
}
