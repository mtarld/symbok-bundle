<?php

namespace Mtarld\SymbokBundle\Tests\Compiler;

use Mtarld\SymbokBundle\Compiler\PassConfig;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @group unit
 * @group compiler
 */
class PassConfigTest extends TestCase
{
    /**
     * @testdox Get separated class and property passes
     */
    public function testGetPasses(): void
    {
        $passes = [
            'class' => [
                'c1',
                'c2',
            ],
            'property' => [
                'p1',
                'p2',
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->will($this->returnCallback(function ($class) {
                return $class;
            }))
        ;

        $config = new PassConfig($container, $passes);

        $classPasses = $config->getClassPasses();
        $propertyPasses = $config->getPropertyPasses();

        $this->assertContains('c1', $classPasses);
        $this->assertContains('c2', $classPasses);
        $this->assertNotContains('p1', $classPasses);
        $this->assertNotContains('p2', $classPasses);

        $this->assertNotContains('c1', $propertyPasses);
        $this->assertNotContains('c2', $propertyPasses);
        $this->assertContains('p1', $propertyPasses);
        $this->assertContains('p2', $propertyPasses);
    }
}
