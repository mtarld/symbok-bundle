<?php

namespace Mtarld\SymbokBundle\Tests;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    protected function setUp(): void
    {
        static::bootKernel();
        static::$container->get('cache_warmer')->warmUp('var/cache/test');
    }
}
