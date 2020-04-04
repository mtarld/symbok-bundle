<?php

namespace Mtarld\SymbokBundle\Tests\Util;

use Mtarld\SymbokBundle\Util\MethodNameGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group util
 */
class MethodNameGeneratorTest extends TestCase
{
    public function testGenerateMethodName(): void
    {
        $this->assertSame('addCar', MethodNameGenerator::generate('car', MethodNameGenerator::METHOD_ADD));
        $this->assertSame('isReady', MethodNameGenerator::generate('ready', MethodNameGenerator::METHOD_IS));

        $this->expectException(\LogicException::class);
        MethodNameGenerator::generate('item', 'unknown');
    }
}
