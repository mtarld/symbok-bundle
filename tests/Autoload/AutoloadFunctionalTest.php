<?php

namespace Mtarld\SymbokBundle\Tests\Autoload;

use Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity\Product1;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group functional
 * @group replacer
 */
class AutoloadFunctionalTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel();
    }

    public function testClassIsReplacedWithAutoload(): void
    {
        $product = new Product1();
        $product->setName('name');

        $this->assertSame('name', $product->getName());
    }
}
