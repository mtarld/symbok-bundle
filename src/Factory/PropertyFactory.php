<?php

namespace Mtarld\SymbokBundle\Factory;

use Mtarld\SymbokBundle\Finder\DocBlockFinder;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Psr\Log\LoggerInterface;

class PropertyFactory
{
    /** @var DocBlockFactory */
    private $docBlockFactory;

    /** @var DocBlockFinder */
    private $docBlockFinder;

    /** @var PhpCodeFinder */
    private $codeFinder;

    /** @var TypeFormatter */
    private $typeFormatter;

    /** @var DoctrineRelationFactory */
    private $relationFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DocBlockFactory $docBlockFactory,
        DocBlockFinder $docBlockFinder,
        PhpCodeFinder $codeFinder,
        TypeFormatter $typeFormatter,
        DoctrineRelationFactory $relationFactory,
        LoggerInterface $logger
    ) {
        $this->docBlockFactory = $docBlockFactory;
        $this->docBlockFinder = $docBlockFinder;
        $this->codeFinder = $codeFinder;
        $this->typeFormatter = $typeFormatter;
        $this->relationFactory = $relationFactory;
        $this->logger = $logger;
    }

    public function create(SymbokClass $class, Property $rawProperty): SymbokProperty
    {
        $this->logger->info('Creating {name} property', ['name' => $rawProperty->props[0]->name->name]);

        $docBlock = $this->docBlockFactory->createFor($rawProperty, $class->getContext());

        $type = (($codeType = $this->codeFinder->findPropertyType($rawProperty)) instanceof Node)
            ? $this->typeFormatter->asDocumentationType($codeType, $class->getContext())
            : $this->docBlockFinder->findType($docBlock)
        ;

        $property = new SymbokProperty(
            $rawProperty->props[0]->name->name,
            $class,
            $type,
            $this->relationFactory->create($class, $rawProperty),
            $this->docBlockFinder->findAnnotations($docBlock)
        );

        $this->logger->info('Property {name} created', ['name' => $property->getName()]);

        return $property;
    }
}
