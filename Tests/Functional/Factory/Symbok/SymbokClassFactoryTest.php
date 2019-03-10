<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Symbok;

use Mtarld\SymbokBundle\Exception\RulesNotComputed\ClassRulesNotComputedException;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokClassFactory;
use Mtarld\SymbokBundle\Model\Rules\ClassRules;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClassMethods;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;
use phpDocumentor\Reflection\DocBlock;

class SymbokClassFactoryTest extends AbstractFunctionalTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadAnnotations();
    }

    public function testCreate()
    {
        $filePath = __DIR__ . '/../../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);

        /** @var SymbokClassFactory $classFactory */
        $classFactory = self::$container->get(SymbokClassFactory::class);
        $class = $classFactory->create($this->nodeClass);
        $this->assertInstanceOf(SymbokClass::class, $class);

        $this->assertSame('Product1', $class->getName());
        $this->assertTrue($class->hasConstructor());
        $this->assertTrue($class->hasToString());
        $this->assertNotNull($class->getDocBlock());
        $this->assertInstanceOf(ClassRules::class, $class->getRules());

        $this->expectException(ClassRulesNotComputedException::class);
        $class = new SymbokClass('', [], new DocBlock(), [], new SymbokClassMethods(false, false));
        $class->getRules();
    }
}
