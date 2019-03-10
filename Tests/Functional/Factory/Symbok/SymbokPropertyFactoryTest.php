<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Symbok;

use Mtarld\SymbokBundle\Exception\RulesNotComputed\PropertyRulesNotComputedException;
use Mtarld\SymbokBundle\Factory\Symbok\SymbokPropertyFactory;
use Mtarld\SymbokBundle\Helper\NodesFinder;
use Mtarld\SymbokBundle\Model\Rules\PropertyRules;
use Mtarld\SymbokBundle\Model\Symbok\SymbokProperty;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyAnnotation;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyMethods;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyTypes;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Mixed_;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class SymbokPropertyFactoryTest extends AbstractFunctionalTest
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

        /** @var SymbokPropertyFactory $propertyFactory */
        $propertyFactory = self::$container->get(SymbokPropertyFactory::class);

        $properties = NodesFinder::findProperties(...$this->nodeClass->stmts);

        /** @var SymbokProperty $property */
        $property = $propertyFactory->create($this->nodeClass, $properties[0]);
        $this->assertInstanceOf(SymbokProperty::class, $property);
        $this->assertSame('id', $property->getName());
        $this->assertInstanceOf(Type::class, $property->getType());
        $this->assertInstanceOf(Type::class, $property->getType());
        $this->assertThat($property->getRelationType(), $this->logicalOr(
            $this->isInstanceOf(Type::class),
            $this->isNull()
        ));
        $this->assertThat($property->getDoctrineRelationAnnotation(), $this->logicalOr(
            $this->isInstanceOf(SymbokPropertyAnnotation::class),
            $this->isNull()
        ));
        $this->assertThat($property->getDoctrineColumnAnnotation(), $this->logicalOr(
            $this->isInstanceOf(SymbokPropertyAnnotation::class),
            $this->isNull()
        ));
        $this->assertNotNull($property->hasAdder());
        $this->assertNotNull($property->hasRemover());
        $this->assertNotNull($property->canUseAdder());
        $this->assertNotNull($property->canUseRemover());
        $this->assertThat($property->isNullable(), $this->logicalOr(
            $this->isType('bool'),
            $this->isNull()
        ));

        $this->assertInstanceOf(PropertyRules::class, $property->getRules());

        $this->expectException(PropertyRulesNotComputedException::class);
        $property = new SymbokProperty(
            '',
            new SymbokPropertyTypes(new Mixed_(), null),
            new SymbokPropertyMethods(false, false, false, false),
            ['all' => [], 'column' => [], 'relation' => []]
        );
        $property->getRules();
    }
}
