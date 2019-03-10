<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Factory\Symbok;

use Mtarld\SymbokBundle\Factory\Symbok\SymbokClassDocBlockFactory;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;

class SymbokClassDocBlockFactoryTest extends AbstractFunctionalTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadAnnotations();
    }

    public function testDocBlock()
    {
        $filePath = __DIR__ . '/../../../Fixtures/files/Product1.php';
        $this->buildContext($filePath);

        /** @var SymbokClassDocBlockFactory $docBlockFactory */
        $docBlockFactory = self::$container->get(SymbokClassDocBlockFactory::class);
        $docBlock = $docBlockFactory->create($this->nodeClass);
        $this->assertSame(2, sizeof($docBlock->getTags()));
        $this->assertSame('Description', $docBlock->getSummary());

        $filePath = __DIR__ . '/../../../Fixtures/files/Product2.php';
        $this->buildContext($filePath);

        $docBlock = $docBlockFactory->create($this->nodeClass);
        $this->assertSame(0, sizeof($docBlock->getTags()));
        $this->assertSame('', $docBlock->getSummary());
    }
}
