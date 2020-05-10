<?php

namespace Mtarld\SymbokBundle\Compiler;

use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\ClassPassInterface;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\PropertyPassInterface;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @final
 */
class PassConfig
{
    /** @var ContainerInterface */
    private $container;

    /** @var array<array<string>> */
    private $passes;

    /**
     * @param array<array<string>> $passes
     */
    public function __construct(
        ContainerInterface $container,
        array $passes
    ) {
        $this->container = $container;
        $this->passes = $passes;
    }

    /**
     * @return array<ClassPassInterface>
     */
    public function getClassPasses(): array
    {
        return array_map(function ($class): ClassPassInterface {
            return $this->container->get($class);
        }, $this->passes['class']);
    }

    /**
     * @return array<PropertyPassInterface>
     */
    public function getPropertyPasses(): array
    {
        return array_map(function ($class): PropertyPassInterface {
            return $this->container->get($class);
        }, $this->passes['property']);
    }
}
