<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Rules;

use Mtarld\SymbokBundle\Factory\Rules\ClassRulesFactory;
use Mtarld\SymbokBundle\Factory\Rules\PropertyRulesFactory;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokClassFactory;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;

class PropertyRulesFactoryTest extends AbstractFunctionalTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadAnnotations();
    }

    public function testCreate()
    {
        /** @var SymbokClassFactory $classFactory */
        $classFactory = self::$container->get(SymbokClassFactory::class);

        /** @var PropertyRulesFactory $rulesFactory */
        $rulesFactory = self::$container->get(PropertyRulesFactory::class);

        $filePath = __DIR__ . '/../../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);

        $class = $classFactory->create($this->nodeClass);
        $property = $class->getProperties()[0];
        $rules = $rulesFactory->create($property);

        $this->assertFalse($rules->requiresGetter());
        $this->assertFalse($rules->requiresSetter());
        $this->assertNull($rules->requiresGetterNullable());
        $this->assertNull($rules->requiresSetterNullable());
        $this->assertTrue($rules->requiresNullable());
        $this->assertFalse($rules->requiresAdder());
        $this->assertFalse($rules->requiresRemover());
        $this->assertNull($rules->requiresSetterFluent());

        $property = $class->getProperties()[1];
        $rules = $rulesFactory->create($property);

        $this->assertFalse($rules->requiresGetter());
        $this->assertTrue($rules->requiresSetter());
        $this->assertNull($rules->requiresGetterNullable());
        $this->assertFalse($rules->requiresSetterNullable());
        $this->assertTrue($rules->requiresNullable());
        $this->assertFalse($rules->requiresAdder());
        $this->assertFalse($rules->requiresRemover());
        $this->assertTrue($rules->requiresSetterFluent());

        $filePath = __DIR__ . '/../../../Fixtures/files/Product2.php';
        $this->buildContext($filePath);

        $class = $classFactory->create($this->nodeClass);
        $property = $class->getProperties()[1];
        $rules = $rulesFactory->create($property);

        $this->assertTrue($rules->requiresGetter());
        $this->assertTrue($rules->requiresSetter());
        $this->assertTrue($rules->requiresGetterNullable());
        $this->assertNull($rules->requiresSetterNullable());
        $this->assertTrue($rules->requiresNullable());
        $this->assertFalse($rules->requiresAdder());
        $this->assertTrue($rules->requiresRemover());
        $this->assertNull($rules->requiresSetterFluent());
    }
}
