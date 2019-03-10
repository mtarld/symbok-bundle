<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Symbok;

use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokClassAnnotationsFactory;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClassAnnotation;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;

class SymbokClassAnnotationsFactoryTest extends AbstractFunctionalTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadAnnotations();
    }

    public function testCreateAndColumnAnnotations()
    {
        $filePath = __DIR__ . '/../../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);

        /** @var SymbokClassAnnotationsFactory $factory */
        $factory = self::$container->get(SymbokClassAnnotationsFactory::class);

        $annotations = $factory->create($this->nodeClass);
        $this->assertIsArray($annotations);
        $this->assertSame(2, sizeof($annotations));
        $this->assertInstanceOf(SymbokClassAnnotation::class, $annotations[0]);
        $this->assertInstanceOf(ToString::class, $annotations[0]->getRealAnnotation());

        $filePath = __DIR__ . '/../../../Fixtures/files/Product2.php';
        $this->buildContext($filePath);

        $annotations = $factory->create($this->nodeClass);
        $this->assertIsArray($annotations);
        $this->assertSame(0, sizeof($annotations));
    }
}
