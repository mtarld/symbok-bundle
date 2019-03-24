<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Rules;

use Mtarld\SymbokBundle\Factory\Rules\ClassRulesFactory;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokClassFactory;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;

class ClassRulesFactoryTest extends AbstractFunctionalTest
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

        /** @var ClassRulesFactory $rulesFactory */
        $rulesFactory = self::$container->get(ClassRulesFactory::class);

        $filePath = __DIR__ . '/../../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);

        $class = $classFactory->create($this->nodeClass);
        $rules = $rulesFactory->create($class);

        $this->assertFalse($rules->requiresAllArgsConstructor());
        $this->assertTrue($rules->requiresAllPropertyGetters());
        $this->assertTrue($rules->requiresAllPropertySetters());
        $this->assertTrue($rules->requiresFluentSetters());
        $this->assertFalse($rules->requiresToString());
        $this->assertFalse($rules->requiresConstructorNullable());
        $this->assertTrue($rules->requiresAllPropertiesNullable());

        $toStringProperties = $rules->getToStringProperties();
        $this->assertContains('id', $toStringProperties);
        $this->assertContains('name', $toStringProperties);

        $filePath = __DIR__ . '/../../../Fixtures/files/Product2.php';
        $this->buildContext($filePath);

        $class = $classFactory->create($this->nodeClass);
        $rules = $rulesFactory->create($class);

        $this->assertFalse($rules->requiresAllArgsConstructor());
        $this->assertFalse($rules->requiresAllPropertyGetters());
        $this->assertFalse($rules->requiresAllPropertySetters());
        $this->assertFalse($rules->requiresFluentSetters());
        $this->assertFalse($rules->requiresToString());
        $this->assertTrue($rules->requiresConstructorNullable());
        $this->assertFalse($rules->requiresAllPropertiesNullable());

        $filePath = __DIR__ . '/../../../Fixtures/files/Product3.php';
        $this->buildContext($filePath);

        $class = $classFactory->create($this->nodeClass);
        $rules = $rulesFactory->create($class);

        $this->assertTrue($rules->requiresAllArgsConstructor());
        $this->assertFalse($rules->requiresAllPropertyGetters());
        $this->assertFalse($rules->requiresAllPropertySetters());
        $this->assertFalse($rules->requiresFluentSetters());
        $this->assertTrue($rules->requiresToString());
        $this->assertFalse($rules->requiresConstructorNullable());
        $this->assertFalse($rules->requiresAllPropertiesNullable());
    }
}
