<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Symbok;

use Mtarld\SymbokBundle\Factory\Symbok\SymbokPropertyAnnotationsFactory;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokPropertyMethodsFactory;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokPropertyTypesFactory;
use Mtarld\SymbokBundle\Helper\NodesFinder;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyMethods;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;

class SymbokPropertyMethodsFactoryTest extends AbstractFunctionalTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadAnnotations();
    }

    public function testMethods()
    {
        $filePath = __DIR__ . '/../../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);

        /** @var SymbokPropertyAnnotationsFactory $annotationsFactory */
        $annotationsFactory = self::$container->get(SymbokPropertyAnnotationsFactory::class);

        /** @var SymbokPropertyTypesFactory $typesFactory */
        $typesFactory = self::$container->get(SymbokPropertyTypesFactory::class);

        /** @var SymbokPropertyMethodsFactory $methodsFactory */
        $methodsFactory = self::$container->get(SymbokPropertyMethodsFactory::class);

        $classProperties = NodesFinder::findProperties(...$this->nodeClass->stmts);
        $property = $classProperties[0];
        $types = $typesFactory->create($property, $annotationsFactory->create($property));

        /** @var SymbokPropertyMethods $methods */
        $methods = $methodsFactory->create($this->nodeClass, $property->props[0], $types);
        $this->assertTrue($methods->hasGetter());
        $this->assertFalse($methods->hasSetter());
        $this->assertFalse($methods->hasAdder());
        $this->assertFalse($methods->hasRemover());

        $property = $classProperties[2];
        $types = $typesFactory->create($property, $annotationsFactory->create($property));

        /** @var SymbokPropertyMethods $methods */
        $methods = $methodsFactory->create($this->nodeClass, $property->props[0], $types);
        $this->assertFalse($methods->hasGetter());
        $this->assertTrue($methods->hasSetter());
        $this->assertFalse($methods->hasAdder());
        $this->assertFalse($methods->hasRemover());

        $filePath = __DIR__ . '/../../../Fixtures/files/Product2.php';
        $this->buildContext($filePath);

        $classProperties = NodesFinder::findProperties(...$this->nodeClass->stmts);
        $property = $classProperties[1];
        $types = $typesFactory->create($property, $annotationsFactory->create($property));

        /** @var SymbokPropertyMethods $methods */
        $methods = $methodsFactory->create($this->nodeClass, $property->props[0], $types);
        $this->assertFalse($methods->hasGetter());
        $this->assertFalse($methods->hasSetter());
        $this->assertTrue($methods->hasAdder());
        $this->assertFalse($methods->hasRemover());
    }
}
