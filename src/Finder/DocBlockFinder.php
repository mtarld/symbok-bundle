<?php

namespace Mtarld\SymbokBundle\Finder;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Annotation;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Mtarld\SymbokBundle\Finder\DocBlock\DoctrineTypes;
use Mtarld\SymbokBundle\Parser\DocBlockParser;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @final
 */
class DocBlockFinder
{
    /** @var DocBlockParser */
    private $parser;

    /** @var DoctrineTypes */
    private $doctrineTypes;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DocBlockParser $parser,
        DoctrineTypes $doctrineTypes,
        LoggerInterface $logger
    ) {
        $this->parser = $parser;
        $this->doctrineTypes = $doctrineTypes;
        $this->logger = $logger;
    }

    /**
     * @return array<mixed>
     */
    public function findAnnotations(DocBlock $docBlock): array
    {
        return $this->parser->parseAnnotations($docBlock);
    }

    public function findType(DocBlock $docBlock): ?Type
    {
        if (($type = $this->getFromVarTag($docBlock)) instanceof Type) {
            $this->logger->debug('Found {type} type from @var tag', ['type' => (string) $type]);

            return $type;
        }

        $annotations = $this->findAnnotations($docBlock);
        if (($type = $this->getFromDoctrineRelation($annotations)) instanceof Type) {
            $this->logger->debug('Found {type} type from doctrine relation', ['type' => (string) $type->getFqsen()]);

            return $type;
        }

        if (($type = $this->getFromDoctrineColumn($annotations)) instanceof Type) {
            $this->logger->debug('Found {type} type from doctrine column', ['type' => (string) $type]);

            return $type;
        }

        $this->logger->debug('Type not found');

        return null;
    }

    public function findDoctrineRelation(DocBlock $docBlock): ?Annotation
    {
        foreach ($this->findAnnotations($docBlock) as $annotation) {
            if ($annotation instanceof Annotation) {
                return $annotation;
            }
        }

        return null;
    }

    private function getFromVarTag(DocBlock $docBlock): ?Type
    {
        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');

        $type = array_key_exists(0, $varTags) ? $varTags[0]->getType() : null;
        if ($type instanceof Compound) {
            $type = new Mixed_();
        }

        return $type instanceof Nullable ? $type->getActualType() : $type;
    }

    /**
     * @param array<mixed> $annotations
     */
    private function getFromDoctrineRelation(array $annotations): ?Object_
    {
        foreach ($annotations as $annotation) {
            if (($type = $this->getCollectionType($annotation)) instanceof Type) {
                return $type;
            }
            if (($type = $this->getTargetEntityType($annotation)) instanceof Type) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @param array<mixed> $annotations
     */
    private function getFromDoctrineColumn(array $annotations): ?Type
    {
        $doctrineTypesMap = $this->doctrineTypes->getTypeMap();
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Column) {
                return $doctrineTypesMap[$annotation->type] ?? null;
            }
        }

        return null;
    }

    /**
     * @param mixed $annotation
     */
    private function getCollectionType($annotation): ?Object_
    {
        return ($annotation instanceof OneToMany || $annotation instanceof ManyToMany) ?
            new Object_(new Fqsen('\\'.Collection::class))
            : null
        ;
    }

    /**
     * @param mixed $annotation
     */
    public function getTargetEntityType($annotation): ?Object_
    {
        return (($annotation instanceof OneToOne || $annotation instanceof ManyToOne) && $targetEntity = $annotation->targetEntity)
            ? new Object_(new Fqsen('\\'.$targetEntity))
            : null
        ;
    }
}
