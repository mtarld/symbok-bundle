<?php

namespace Mtarld\SymbokBundle\Factory;

use Mtarld\SymbokBundle\Finder\DocBlockFinder;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt\Property;
use Psr\Log\LoggerInterface;

class ClassFactory
{
    private $propertyFactory;
    private $phpCodeFinder;
    private $docBlockFinder;
    private $docBlockFactory;
    private $logger;

    public function __construct(
        PropertyFactory $propertyFactory,
        PhpCodeFinder $phpCodeFinder,
        DocBlockFinder $docBlockFinder,
        DocBlockFactory $docBlockFactory,
        LoggerInterface $symbokLogger
    ) {
        $this->propertyFactory = $propertyFactory;
        $this->phpCodeFinder = $phpCodeFinder;
        $this->docBlockFinder = $docBlockFinder;
        $this->docBlockFactory = $docBlockFactory;
        $this->logger = $symbokLogger;
    }

    public function create(array $statements): SymbokClass
    {
        $context = new Context(
            $this->phpCodeFinder->findNamespace($statements)->name,
            $this->phpCodeFinder->findAliases($statements)
        );

        $rawClass = $this->phpCodeFinder->findClass($statements);
        $docBlock = $this->docBlockFactory->createFor($rawClass, $context);

        $this->logger->info('Parsing {class}', ['class' => $rawClass->name->name]);

        $class = (new SymbokClass())
               ->setName($rawClass->name->name)
               ->setStatements($rawClass->stmts)
               ->setDocBlock($docBlock)
               ->setAnnotations($this->docBlockFinder->findAnnotations($docBlock))
               ->setContext($context)
        ;

        $class = $class->setProperties($this->createProperties($class));

        $this->logger->info('{class} parsed', ['class' => (string) $class]);

        return $class;
    }

    private function createProperties(SymbokClass $class): array
    {
        return array_map(function (Property $property) use ($class) {
            return $this->propertyFactory->create($class, $property);
        }, $this->phpCodeFinder->findProperties($class->getStatements()));
    }
}
