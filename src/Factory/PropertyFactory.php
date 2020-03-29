<?php

namespace Mtarld\SymbokBundle\Factory;

use Mtarld\SymbokBundle\Finder\DocBlockFinder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PhpParser\Node\Stmt\Property;
use Psr\Log\LoggerInterface;

class PropertyFactory
{
    /** @var DocBlockFactory */
    private $docBlockFactory;

    /** @var DocBlockFinder */
    private $docBlockFinder;

    /** @var DoctrineRelationFactory */
    private $relationFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DocBlockFactory $docBlockFactory,
        DocBlockFinder $docBlockFinder,
        DoctrineRelationFactory $relationFactory,
        LoggerInterface $logger
    ) {
        $this->docBlockFactory = $docBlockFactory;
        $this->docBlockFinder = $docBlockFinder;
        $this->relationFactory = $relationFactory;
        $this->logger = $logger;
    }

    public function create(SymbokClass $class, Property $rawProperty): SymbokProperty
    {
        $docBlock = $this->docBlockFactory->createFor($rawProperty, $class->getContext());

        $property = new SymbokProperty(
            $rawProperty->props[0]->name->name,
            $class,
            $this->docBlockFinder->findType($docBlock),
            $this->relationFactory->create($class, $rawProperty),
            $this->docBlockFinder->findAnnotations($docBlock)
        );

        $this->logger->info('Property {name} created', [
            'name' => $property->getName(),
        ]);

        return $property;
    }
}
