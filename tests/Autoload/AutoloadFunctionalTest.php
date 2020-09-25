<?php

namespace Mtarld\SymbokBundle\Tests\Autoload;

use App\Entity\Product1;
use App\Entity\ProductInterface;
use Mtarld\SymbokBundle\Tests\KernelTestCase;

/**
 * @group functional
 * @group replacer
 * @group autoload
 */
class AutoloadFunctionalTest extends KernelTestCase
{
    public function testClassIsReplacedWithAutoload(): void
    {
        $product = new Product1();
        $product->setName('name');

        $this->assertSame('name', $product->getName());
    }

    public function testInterfaceIsNotReplacedWithAutoload(): void
    {
        $this->assertFalse(method_exists(ProductInterface::class, '__toString'));
    }
}
