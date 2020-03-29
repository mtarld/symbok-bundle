<?php

namespace Mtarld\SymbokBundle\Factory;

use Mtarld\SymbokBundle\Exception\CodeFindingException;
use Mtarld\SymbokBundle\Finder\DocBlockFinder;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Psr\Log\LoggerInterface;

class ClassFactory
{
    /** @var PropertyFactory */
    private $propertyFactory;

    /** @var PhpCodeFinder */
    private $phpCodeFinder;

    /** @var DocBlockFinder */
    private $docBlockFinder;

    /** @var DocBlockFactory */
    private $docBlockFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        PropertyFactory $propertyFactory,
        PhpCodeFinder $phpCodeFinder,
        DocBlockFinder $docBlockFinder,
        DocBlockFactory $docBlockFactory,
        LoggerInterface $logger
    ) {
        $this->propertyFactory = $propertyFactory;
        $this->phpCodeFinder = $phpCodeFinder;
        $this->docBlockFinder = $docBlockFinder;
        $this->docBlockFactory = $docBlockFactory;
        $this->logger = $logger;
    }

    /**
     * @param array<Node> $statements
     */
    public function create(array $statements): SymbokClass
    {
        $context = new Context($this->phpCodeFinder->findNamespaceName($statements), $this->phpCodeFinder->findAliases($statements));
        $rawClass = $this->phpCodeFinder->findClass($statements);
        $docBlock = $this->docBlockFactory->createFor($rawClass, $context);

        if (null === $rawClass->name) {
            throw new CodeFindingException('Cannot retrieve class name');
        }

        $this->logger->info('Parsing {class}', ['class' => $rawClass->name]);

        $class = new SymbokClass(
            (string) $rawClass->name,
            $rawClass->stmts,
            $docBlock,
            [],
            $this->docBlockFinder->findAnnotations($docBlock),
            $context
        );
        $class = $class->setProperties($this->createProperties($class));

        $this->logger->info('{class} parsed', ['class' => (string) $class]);

        return $class;
    }

    /**
     * @return array<SymbokProperty>
     */
    private function createProperties(SymbokClass $class): array
    {
        return array_map(function (Property $property) use ($class): SymbokProperty {
            return $this->propertyFactory->create($class, $property);
        }, $this->phpCodeFinder->findProperties($class->getStatements()));
    }
}
