<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Symbok;

use Mtarld\SymbokBundle\Factory\Symbok\SymbokClassMethodsFactory;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClassMethods;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;

class SymbokClassMethodsFactoryTest extends AbstractFunctionalTest
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

        /** @var SymbokClassMethodsFactory $methodsFactory */
        $methodsFactory = self::$container->get(SymbokClassMethodsFactory::class);
        $methods = $methodsFactory->create($this->nodeClass);
        $this->assertInstanceOf(SymbokClassMethods::class, $methods);
        $this->assertTrue($methods->hasConstructor());
        $this->assertTrue($methods->hasToString());
    }
}
