<?php

namespace Mtarld\SymbokBundle\Tests\Compiler;

use Mtarld\SymbokBundle\Compiler\PassConfig;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\AllArgsConstructorPass;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\GetterPass;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\SetterPass;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\ToStringPass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group functional
 * @group compiler
 */
class PassConfigTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel();
    }

    /**
     * @testdox Get separated class and property passes
     */
    public function testGetPasses(): void
    {
        $passes = [
            'class' => [
                AllArgsConstructorPass::class,
                ToStringPass::class,
            ],
            'property' => [
                GetterPass::class,
                SetterPass::class,
            ],
        ];

        $config = new PassConfig(static::$container, $passes);

        $classPasses = $config->getClassPasses();
        $this->assertContains(static::$container->get(AllArgsConstructorPass::class), $classPasses);
        $this->assertContains(static::$container->get(ToStringPass::class), $classPasses);
        $this->assertNotContains(static::$container->get(GetterPass::class), $classPasses);
        $this->assertNotContains(static::$container->get(SetterPass::class), $classPasses);

        $propertyPasses = $config->getPropertyPasses();
        $this->assertNotContains(static::$container->get(AllArgsConstructorPass::class), $propertyPasses);
        $this->assertNotContains(static::$container->get(ToStringPass::class), $propertyPasses);
        $this->assertContains(static::$container->get(GetterPass::class), $propertyPasses);
        $this->assertContains(static::$container->get(SetterPass::class), $propertyPasses);
    }
}
