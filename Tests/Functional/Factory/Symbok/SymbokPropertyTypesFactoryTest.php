<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Symbok;

use Doctrine\Common\Collections\Collection;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokPropertyAnnotationsFactory;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokPropertyTypesFactory;
use Mtarld\SymbokBundle\Helper\NodesFinder;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyTypes;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;

class SymbokPropertyTypesFactoryTest extends AbstractFunctionalTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadAnnotations();
    }

    public function testCreateTypes()
    {
        $filePath = __DIR__ . '/../../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);

        /** @var SymbokPropertyAnnotationsFactory $annotationsFactory */
        $annotationsFactory = self::$container->get(SymbokPropertyAnnotationsFactory::class);

        /** @var SymbokPropertyTypesFactory $typesFactory */
        $typesFactory = self::$container->get(SymbokPropertyTypesFactory::class);

        $classProperties = NodesFinder::findProperties(...$this->nodeClass->stmts);
        $property = $classProperties[0];
        /** @var SymbokPropertyTypes $types */
        $types = $typesFactory->create($property, $annotationsFactory->create($property));
        $this->assertInstanceOf(Integer::class, $types->getBaseType());
        $this->assertNull($types->getRelationType());

        $property = $classProperties[1];
        /** @var SymbokPropertyTypes $types */
        $types = $typesFactory->create($property, $annotationsFactory->create($property));
        $this->assertInstanceOf(Nullable::class, $types->getBaseType());
        $this->assertNull($types->getRelationType());

        $property = $classProperties[2];
        /** @var SymbokPropertyTypes $types */
        $types = $typesFactory->create($property, $annotationsFactory->create($property));
        $this->assertInstanceOf(Mixed_::class, $types->getBaseType());
        $this->assertNull($types->getRelationType());

        $filePath = __DIR__ . '/../../../Fixtures/files/Product2.php';
        $this->buildContext($filePath);

        $classProperties = NodesFinder::findProperties(...$this->nodeClass->stmts);
        $property = $classProperties[0];
        /** @var SymbokPropertyTypes $types */
        $types = $typesFactory->create($property, $annotationsFactory->create($property));
        $this->assertInstanceOf(Integer::class, $types->getBaseType());
        $this->assertNull($types->getRelationType());

        $classProperties = NodesFinder::findProperties(...$this->nodeClass->stmts);
        $property = $classProperties[0];
        /** @var SymbokPropertyTypes $types */
        $types = $typesFactory->create($property, $annotationsFactory->create($property));
        $this->assertInstanceOf(Integer::class, $types->getBaseType());
        $this->assertNull($types->getRelationType());

        $property = $classProperties[1];
        /** @var SymbokPropertyTypes $types */
        $types = $typesFactory->create($property, $annotationsFactory->create($property));
        $this->assertInstanceOf(Object_::class, $types->getBaseType());
        $collectionFqsen = new Fqsen('\\' . Collection::class);
        $this->assertSame((string)$collectionFqsen, (string)$types->getBaseType()->getFqsen());


        $this->assertInstanceOf(Object_::class, $types->getRelationType());
        $entityFqsen = new Fqsen('\\App\\Entity\\Price');
        $this->assertSame((string)$entityFqsen, (string)$types->getRelationType()->getFqsen());

        $property = $classProperties[2];
        /** @var PropertyTypes $types */
        $types = $typesFactory->create($property, $annotationsFactory->create($property));

        $this->assertInstanceOf(Object_::class, $types->getBaseType());
        $entityFqsen = new Fqsen('\\App\\Entity\\Category');
        $this->assertSame((string)$entityFqsen, (string)$types->getBaseType()->getFqsen());

        $this->assertNull($types->getRelationType());
    }
}
