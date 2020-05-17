<?php

namespace App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Mtarld\SymbokBundle\SymbokBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new SymbokBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->setParameter('kernel.project_dir', __DIR__.'/..');

        $loader->load(__DIR__.'/../config/services.yaml');
        $loader->load(__DIR__.'/../config/packages/doctrine.yaml');
        $loader->load(__DIR__.'/../config/packages/framework.yaml');
        $loader->load(__DIR__.'/../config/packages/symbok.yaml');
    }
}
