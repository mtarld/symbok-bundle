<?php

namespace Mtarld\SymbokBundle\Tests\Compiler;

use Mtarld\SymbokBundle\Compiler\PassConfig;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\ClassPassInterface;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\PropertyPassInterface;
use Mtarld\SymbokBundle\Factory\ClassFactory;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @group unit
 * @group compiler
 */
class RuntimeClassCompilerTest extends TestCase
{
    public function testExecuteSupportedClassPasses(): void
    {
        $classFactory = $this->createMock(ClassFactory::class);

        $classPassOne = $this->createMock(ClassPassInterface::class);
        $classPassOne
            ->expects($this->exactly(1))
            ->method('support')
            ->willReturn(true)
        ;

        $classPassOne
            ->expects($this->exactly(1))
            ->method('process')
        ;

        $classPassTwo = $this->createMock(ClassPassInterface::class);
        $classPassTwo
            ->expects($this->exactly(1))
            ->method('support')
            ->willReturn(false)
        ;

        $classPassTwo
            ->expects($this->exactly(0))
            ->method('process')
        ;

        $passConfig = $this->createMock(PassConfig::class);
        $passConfig
            ->expects($this->exactly(1))
            ->method('getClassPasses')
            ->willReturn([$classPassOne, $classPassTwo])
        ;

        $logger = $this->createMock(LoggerInterface::class);

        (new RuntimeClassCompiler($passConfig, $classFactory, $logger))->compile([]);
    }

    public function testExecuteSupportedPropertyPasses(): void
    {
        $class = $this->createMock(SymbokClass::class);
        $class
            ->expects($this->exactly(1))
            ->method('getProperties')
            ->willReturn([
                $this->createMock(SymbokProperty::class),
                $this->createMock(SymbokProperty::class),
            ])
        ;

        $classFactory = $this->createMock(ClassFactory::class);
        $classFactory
            ->expects($this->exactly(1))
            ->method('create')
            ->willReturn($class)
        ;

        $propertyPassOne = $this->createMock(PropertyPassInterface::class);
        $propertyPassOne
            ->expects($this->exactly(2))
            ->method('support')
            ->willReturn(true)
        ;

        $propertyPassOne
            ->expects($this->exactly(2))
            ->method('process')
        ;

        $propertyPassTwo = $this->createMock(PropertyPassInterface::class);
        $propertyPassTwo
            ->expects($this->exactly(2))
            ->method('support')
            ->willReturn(false)
        ;

        $propertyPassTwo
            ->expects($this->exactly(0))
            ->method('process')
        ;

        $passConfig = $this->createMock(PassConfig::class);
        $passConfig
            ->expects($this->exactly(2))
            ->method('getPropertyPasses')
            ->willReturn([$propertyPassOne, $propertyPassTwo])
        ;

        $logger = $this->createMock(LoggerInterface::class);

        (new RuntimeClassCompiler($passConfig, $classFactory, $logger))->compile([]);
    }
}
