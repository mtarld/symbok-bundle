<?php

namespace Mtarld\SymbokBundle\Compiler;

use Psr\Container\ContainerInterface;

class PassConfig
{
    private $container;
    private $passes;

    public function __construct(
        ContainerInterface $container,
        array $passes
    ) {
        $this->container = $container;
        $this->passes = $passes;
    }

    public function getClassPasses(): array
    {
        return array_map(function ($class) {
            return $this->container->get($class);
        }, $this->passes['class']);
    }

    public function getPropertyPasses(): array
    {
        return array_map(function ($class) {
            return $this->container->get($class);
        }, $this->passes['property']);
    }
}
