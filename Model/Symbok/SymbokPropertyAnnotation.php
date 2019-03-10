<?php

namespace Mtarld\SymbokBundle\Model\Symbok;

class SymbokPropertyAnnotation
{
    const TYPE_STANDARD = 'Standard';
    const TYPE_DOCTRINE_ENTITY = 'DoctrineEntity';
    const TYPE_DOCTRINE_COLLECTION = 'DoctrineCollection';
    const TYPE_DOCTRINE_COLUMN = 'DoctrineColumn';

    private $realAnnotation;

    /** @var string */
    private $type;

    public function __construct($realAnnotation, string $type)
    {
        $this->realAnnotation = $realAnnotation;
        $this->type = $type;
    }

    public function getRealAnnotation()
    {
        return $this->realAnnotation;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isDoctrineRelationAnnotation(): bool
    {
        return in_array($this->type, [
            self::TYPE_DOCTRINE_ENTITY,
            self::TYPE_DOCTRINE_COLLECTION
        ]);
    }

    public function isDoctrineColumnAnnotation(): bool
    {
        return $this->type == self::TYPE_DOCTRINE_COLUMN;
    }

    public function isDoctrineCollectionAnnotation(): bool
    {
        return $this->type == self::TYPE_DOCTRINE_COLLECTION;
    }

    public function isDoctrineEntityAnnotation(): bool
    {
        return $this->type == self::TYPE_DOCTRINE_ENTITY;
    }
}
