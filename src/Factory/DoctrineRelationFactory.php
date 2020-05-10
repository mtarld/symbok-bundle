<?php

namespace Mtarld\SymbokBundle\Factory;

use Doctrine\ORM\Mapping\Annotation;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Mtarld\SymbokBundle\Finder\DocBlockFinder;
use Mtarld\SymbokBundle\Model\Relation\DoctrineRelation;
use Mtarld\SymbokBundle\Model\Relation\ManyToManyRelation;
use Mtarld\SymbokBundle\Model\Relation\ManyToOneRelation;
use Mtarld\SymbokBundle\Model\Relation\OneToManyRelation;
use Mtarld\SymbokBundle\Model\Relation\OneToOneRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use PhpParser\Node\Stmt\Property;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @final
 */
class DoctrineRelationFactory
{
    /** @var DocBlockFactory */
    private $docBlockFactory;

    /** @var DocBlockFinder */
    private $docBlockFinder;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DocBlockFactory $docBlockFactory,
        DocBlockFinder $docBlockFinder,
        LoggerInterface $logger
    ) {
        $this->docBlockFactory = $docBlockFactory;
        $this->docBlockFinder = $docBlockFinder;
        $this->logger = $logger;
    }

    public function create(SymbokClass $class, Property $property): ?DoctrineRelation
    {
        $annotation = $this->getDoctrineAnnotation($class, $property);
        if (!$annotation instanceof Annotation) {
            return null;
        }

        switch (get_class($annotation)) {
            case ManyToOne::class:
                $relation = $this->createManyToOneRelation($class, $annotation);
                break;
            case ManyToMany::class:
                $relation = $this->createManyToManyRelation($class, $annotation);
                break;
            case OneToOne::class:
                $relation = $this->createOneToOneRelation($class, $annotation);
                break;
            case OneToMany::class:
                $relation = $this->createOneToManyRelation($class, $annotation);
                break;
            default:
                return null;
        }

        $this->logger->debug('Found {relation} relation with {target}', [
            'relation' => get_class($relation),
            'target' => $relation->getTargetClassName(),
            'owning' => $relation->isOwning(),
        ]);

        return $relation;
    }

    private function getDoctrineAnnotation(SymbokClass $class, Property $property): ?Annotation
    {
        $docBlock = $this->docBlockFactory->createFor($property, $class->getContext());

        return $this->docBlockFinder->findDoctrineRelation($docBlock);
    }

    private function createOneToManyRelation(SymbokClass $class, OneToMany $annotation): OneToManyRelation
    {
        $relation = new OneToManyRelation();
        $relation
            ->setClassName($class->getName())
            ->setTargetClassName($annotation->targetEntity)
            ->setTargetPropertyName($annotation->mappedBy)
        ;

        return $relation;
    }

    private function createOneToOneRelation(SymbokClass $class, OneToOne $annotation): OneToOneRelation
    {
        $isOwning = !empty($annotation->inversedBy);

        $relation = new OneToOneRelation();
        $relation
            ->setClassName($class->getName())
            ->setTargetClassName($annotation->targetEntity)
            ->setTargetPropertyName(true === $isOwning ? $annotation->inversedBy : $annotation->mappedBy)
            ->setIsOwning($isOwning)
        ;

        return $relation;
    }

    private function createManyToOneRelation(SymbokClass $class, ManyToOne $annotation): ManyToOneRelation
    {
        $relation = new ManyToOneRelation();
        $relation
            ->setClassName($class->getName())
            ->setTargetClassName($annotation->targetEntity)
            ->setTargetPropertyName($annotation->inversedBy)
        ;

        return $relation;
    }

    private function createManyToManyRelation(SymbokClass $class, ManyToMany $annotation): ManyToManyRelation
    {
        $isOwning = !empty($annotation->inversedBy);
        $relation = new ManyToManyRelation();
        $relation
            ->setClassName($class->getName())
            ->setTargetClassName($annotation->targetEntity)
            ->setTargetPropertyName(true === $isOwning ? $annotation->inversedBy : $annotation->mappedBy)
            ->setIsOwning($isOwning)
        ;

        return $relation;
    }
}
