<?php

namespace Mtarld\SymbokBundle\Model\Symbok;

use Mtarld\SymbokBundle\Annotation\Nullable;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Exception\RulesNotComputed\PropertyRulesNotComputedException;
use Mtarld\SymbokBundle\Model\Rules\PropertyRules;
use phpDocumentor\Reflection\Type;

class SymbokProperty
{
    /** @var string */
    private $name;

    /** @var SymbokPropertyTypes */
    private $types;

    /** @var SymbokPropertyAnnotation[] */
    private $annotations = [];

    /** @var SymbokPropertyMethods */
    private $methods;

    /** @var bool */
    private $noAdd = false;

    /** @var bool */
    private $noRemove = false;

    /** @var bool|null */
    private $nullable;

    /** @var PropertyRules */
    private $rules;

    public function __construct(
        string $name,
        SymbokPropertyTypes $types,
        SymbokPropertyMethods $methods,
        array $annotations
    ) {
        $this->name = $name;
        $this->types = $types;
        $this->annotations = $annotations;
        $this->methods = $methods;
        $this->proceedAnnotations();
    }

    private function proceedAnnotations(): void
    {
        foreach ($this->getAnnotations() as $annotation) {
            /** @var SymbokPropertyAnnotation $annotation */
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

    public function getType(): Type
    {
        return $this->types->getBaseType();
    }

    public function getRelationType(): ?Type
    {
        return $this->types->getRelationType();
    }

    public function getDoctrineRelationAnnotation(): ?SymbokPropertyAnnotation
    {
        return $this->annotations['relation'];
    }

    public function getDoctrineColumnAnnotation(): ?SymbokPropertyAnnotation
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

    public function getRules(): PropertyRules
    {
        if (!$this->rules) {
            throw new PropertyRulesNotComputedException();
        }

        return $this->rules;
    }

    public function setRules(PropertyRules $rules): void
    {
        $this->rules = $rules;
    }
}
