<?php

namespace Mtarld\SymbokBundle\Factory;

use Mtarld\SymbokBundle\Finder\DocBlockFinder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PhpParser\Node\Stmt\Property;
use Psr\Log\LoggerInterface;

class PropertyFactory
{
    private $docBlockFactory;
    private $docBlockFinder;
    private $relationFactory;
    private $logger;

    public function __construct(
        DocBlockFactory $docBlockFactory,
        DocBlockFinder $docBlockFinder,
        DoctrineRelationFactory $relationFactory,
        LoggerInterface $symbokLogger
    ) {
        $this->docBlockFactory = $docBlockFactory;
        $this->docBlockFinder = $docBlockFinder;
        $this->relationFactory = $relationFactory;
        $this->logger = $symbokLogger;
    }

    public function create(SymbokClass $class, Property $property): SymbokProperty
    {
        $docBlock = $this->docBlockFactory->createFor($property, $class->getContext());

        $property = (new SymbokProperty())
            ->setName($property->props[0]->name->name)
            ->setClass($class)
            ->setAnnotations($this->docBlockFinder->findAnnotations($docBlock))
            ->setType($this->docBlockFinder->findType($docBlock))
            ->setRelation($this->relationFactory->create($class, $property))
        ;

        $this->logger->info('Property {name} created', [
            'name' => $property->getName(),
        ]);

        return $property;
    }
}
