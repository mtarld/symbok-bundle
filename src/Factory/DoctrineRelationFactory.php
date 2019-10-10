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

class DoctrineRelationFactory
{
    private $docBlockFactory;
    private $docBlockFinder;
    private $logger;

    public function __construct(
        DocBlockFactory $docBlockFactory,
        DocBlockFinder $docBlockFinder,
        LoggerInterface $symbokLogger
    ) {
        $this->docBlockFactory = $docBlockFactory;
        $this->docBlockFinder = $docBlockFinder;
        $this->logger = $symbokLogger;
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
        return (new OneToManyRelation())
            ->setClassName($class->getName())
            ->setTargetClassName($annotation->targetEntity)
            ->setTargetPropertyName($annotation->mappedBy)
        ;
    }

    private function createOneToOneRelation(SymbokClass $class, OneToOne $annotation): OneToOneRelation
    {
        $isOwning = !empty($annotation->inversedBy);

        return (new OneToOneRelation())
            ->setClassName($class->getName())
            ->setTargetClassName($annotation->targetEntity)
            ->setTargetPropertyName(true === $isOwning ? $annotation->inversedBy : $annotation->mappedBy)
            ->setIsOwning($isOwning)
        ;
    }

    private function createManyToOneRelation(SymbokClass $class, ManyToOne $annotation): ManyToOneRelation
    {
        return (new ManyToOneRelation())
            ->setClassName($class->getName())
            ->setTargetClassName($annotation->targetEntity)
            ->setTargetPropertyName($annotation->inversedBy)
        ;
    }

    private function createManyToManyRelation(SymbokClass $class, ManyToMany $annotation): ManyToManyRelation
    {
        $isOwning = !empty($annotation->inversedBy);

        return (new ManyToManyRelation())
            ->setClassName($class->getName())
            ->setTargetClassName($annotation->targetEntity)
            ->setTargetPropertyName(true === $isOwning ? $annotation->inversedBy : $annotation->mappedBy)
            ->setIsOwning($isOwning)
        ;
    }
}
