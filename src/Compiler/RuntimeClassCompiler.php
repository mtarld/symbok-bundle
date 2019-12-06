<?php

namespace Mtarld\SymbokBundle\Compiler;

use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\ClassPassInterface;
use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler\PropertyPassInterface;
use Mtarld\SymbokBundle\Factory\ClassFactory;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Psr\Log\LoggerInterface;

class RuntimeClassCompiler implements CompilerInterface
{
    private $config;
    private $classFactory;
    private $logger;

    public function __construct(
        PassConfig $config,
        ClassFactory $classFactory,
        LoggerInterface $symbokLogger
    ) {
        $this->config = $config;
        $this->classFactory = $classFactory;
        $this->logger = $symbokLogger;
    }

    public function compile(array $statements): SymbokClass
    {
        $class = $this->classFactory->create($statements);

        $this->logger->info('Compiling {class}', ['class' => (string) $class]);

        $classPasses = array_filter($this->config->getClassPasses(), function (ClassPassInterface $pass) use ($class) {
            return $pass->support($class);
        });

        $class = array_reduce($classPasses, function (SymbokClass $class, ClassPassInterface $pass) {
            $this->logger->info('Run {pass} compiler pass on {class}', ['pass' => get_class($pass), 'class' => (string) $class]);

            return $pass->process($class);
        }, $class);

        foreach ($class->getProperties() as $property) {
            $propertyPasses = array_filter($this->config->getPropertyPasses(), function (PropertyPassInterface $pass) use ($property) {
                return $pass->support($property);
            });

            $class = array_reduce($propertyPasses, function (SymbokClass $class, PropertyPassInterface $pass) use ($property) {
                $this->logger->info('Run {pass} compiler pass on {class}', ['pass' => get_class($pass), 'class' => (string) $class]);

                return $pass->process($property);
            }, $class);
        }

        $this->logger->info('{class} compiled', ['class' => (string) $class]);

        return $class;
    }
}
