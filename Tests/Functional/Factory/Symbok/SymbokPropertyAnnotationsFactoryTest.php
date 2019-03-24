<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Symbok;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokPropertyAnnotationsFactory;
use Mtarld\SymbokBundle\Helper\NodesFinder;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyAnnotation;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;

class SymbokPropertyAnnotationsFactoryTest extends AbstractFunctionalTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadAnnotations();
    }

    public function testCreateSymbokAndColumnAnnotations()
    {
        $filePath = __DIR__ . '/../../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);

        /** @var SymbokPropertyAnnotationsFactory $factory */
        $factory = self::$container->get(SymbokPropertyAnnotationsFactory::class);
        $classProperties = NodesFinder::findProperties(...$this->nodeClass->stmts);

        $idProperty = $classProperties[0];
        $annotations = $factory->create($idProperty);
        $this->assertArrayHasKey('all', $annotations);
        $this->assertArrayHasKey('column', $annotations);
        $this->assertArrayHasKey('relation', $annotations);
        $this->assertEquals(2, sizeof($annotations['all']));
        $this->assertInstanceOf(SymbokPropertyAnnotation::class, $annotations['column']);
        $this->assertNull($annotations['relation']);

        /** @var SymbokPropertyAnnotation $doctrineAnnotation */
        $doctrineAnnotation = $annotations['all'][0];
        $this->assertSame(SymbokPropertyAnnotation::TYPE_DOCTRINE_COLUMN, $doctrineAnnotation->getType());
        $this->assertTrue($doctrineAnnotation->isDoctrineColumnAnnotation());
        $this->assertFalse($doctrineAnnotation->isDoctrineEntityAnnotation());
        $this->assertFalse($doctrineAnnotation->isDoctrineCollectionAnnotation());
        $this->assertFalse($doctrineAnnotation->isDoctrineRelationAnnotation());

        /** @var Column $realDoctrineAnnotation */
        $realDoctrineAnnotation = $doctrineAnnotation->getRealAnnotation();
        $this->assertInstanceOf(Column::class, $realDoctrineAnnotation);
        $this->assertSame('integer', $realDoctrineAnnotation->type);
        $this->assertTrue($realDoctrineAnnotation->nullable);
        $this->assertNull($realDoctrineAnnotation->length);

        /** @var SymbokPropertyAnnotation $getterAnnotation */
        $getterAnnotation = $annotations['all'][1];
        $this->assertSame(SymbokPropertyAnnotation::TYPE_STANDARD, $getterAnnotation->getType());
        $this->assertFalse($getterAnnotation->isDoctrineColumnAnnotation());
        $this->assertFalse($getterAnnotation->isDoctrineEntityAnnotation());
        $this->assertFalse($getterAnnotation->isDoctrineCollectionAnnotation());
        $this->assertFalse($getterAnnotation->isDoctrineRelationAnnotation());

        /** @var Getter $realGetterAnnotation */
        $realGetterAnnotation = $getterAnnotation->getRealAnnotation();
        $this->assertInstanceOf(Getter::class, $realGetterAnnotation);
        $this->assertNull($realGetterAnnotation->nullable);
    }

    public function testCreateNoAndRelationAndEntityAnnotations()
    {
        $filePath = __DIR__ . '/../../../Fixtures/files/Product2.php';
        $this->buildContext($filePath);

        /** @var SymbokPropertyAnnotationsFactory $factory */
        $factory = self::$container->get(SymbokPropertyAnnotationsFactory::class);
        $classProperties = NodesFinder::findProperties(...$this->nodeClass->stmts);

        $idProperty = $classProperties[0];
        $annotations = $factory->create($idProperty);
        $this->assertEquals(0, sizeof($annotations['all']));
        $this->assertNull($annotations['column']);
        $this->assertNull($annotations['relation']);

        $priceProperty = $classProperties[1];
        $annotations = $factory->create($priceProperty);
        $this->assertEquals(4, sizeof($annotations['all']));
        $this->assertNull($annotations['column']);
        $this->assertInstanceOf(SymbokPropertyAnnotation::class, $annotations['relation']);

        /** @var SymbokPropertyAnnotation $doctrineAnnotation */
        $doctrineAnnotation = $annotations['all'][0];
        $this->assertSame(SymbokPropertyAnnotation::TYPE_DOCTRINE_COLLECTION, $doctrineAnnotation->getType());
        $this->assertFalse($doctrineAnnotation->isDoctrineColumnAnnotation());
        $this->assertFalse($doctrineAnnotation->isDoctrineEntityAnnotation());
        $this->assertTrue($doctrineAnnotation->isDoctrineCollectionAnnotation());
        $this->assertTrue($doctrineAnnotation->isDoctrineRelationAnnotation());

        /** @var ManyToMany $realDoctrineAnnotation */
        $realDoctrineAnnotation = $doctrineAnnotation->getRealAnnotation();
        $this->assertInstanceOf(ManyToMany::class, $realDoctrineAnnotation);

        /** @var SymbokPropertyAnnotation $joinAnnotation */
        $joinAnnotation = $annotations['all'][1];
        $this->assertSame(SymbokPropertyAnnotation::TYPE_STANDARD, $joinAnnotation->getType());

        $categoryProperty = $classProperties[2];
        $annotations = $factory->create($categoryProperty);
        $this->assertEquals(3, sizeof($annotations['all']));
        $this->assertNull($annotations['column']);
        $this->assertInstanceOf(SymbokPropertyAnnotation::class, $annotations['relation']);

        /** @var SymbokPropertyAnnotation $doctrineAnnotation */
        $doctrineAnnotation = $annotations['all'][0];
        $this->assertSame(SymbokPropertyAnnotation::TYPE_DOCTRINE_ENTITY, $doctrineAnnotation->getType());
        $this->assertFalse($doctrineAnnotation->isDoctrineColumnAnnotation());
        $this->assertTrue($doctrineAnnotation->isDoctrineEntityAnnotation());
        $this->assertFalse($doctrineAnnotation->isDoctrineCollectionAnnotation());
        $this->assertTrue($doctrineAnnotation->isDoctrineRelationAnnotation());

        /** @var ManyToOne $realDoctrineAnnotation */
        $realDoctrineAnnotation = $doctrineAnnotation->getRealAnnotation();
        $this->assertInstanceOf(ManyToOne::class, $realDoctrineAnnotation);

        /** @var SymbokPropertyAnnotation $setterAnnotation */
        $setterAnnotation = $annotations['all'][1];
        $this->assertSame(SymbokPropertyAnnotation::TYPE_STANDARD, $setterAnnotation->getType());
    }
}
