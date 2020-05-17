<?php

namespace Mtarld\SymbokBundle;

use Mtarld\SymbokBundle\Autoload\Autoloader;
use Mtarld\SymbokBundle\Autoload\DoctrineMetadataPathReplacer;
use Mtarld\SymbokBundle\DependencyInjection\SymbokExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @final
 */
class SymbokBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new SymbokExtension();
    }

    public function boot(): void
    {
        /** @var Autoloader $autoloader */
        $autoloader = $this->container->get('symbok.autoloader');
        $autoloader->register();

        /** @var DoctrineMetadataPathReplacer $doctrineMetadataPathReplacer */
        $doctrineMetadataPathReplacer = $this->container->get('symbok.autoload.doctrine_metadata_path_replacer');
        $doctrineMetadataPathReplacer->replaceWithSymbokPath();
    }
}
