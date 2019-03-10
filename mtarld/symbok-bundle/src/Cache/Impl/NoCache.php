<?php

namespace Mtarld\SymbokBundle\Cache\Impl;

use Mtarld\SymbokBundle\Cache\CacheInterface;

class NoCache implements CacheInterface
{
    /** @var array */
    private $code = [];

    public function exists(string $className): bool
    {
        return false;
    }

    public function load(string $className): void
    {
        if (array_key_exists($className, $this->code)) {
            eval($this->code[$className]);
        }
    }

    public function write(string $className, string $content): void
    {
        $this->code[$className] = $content;
    }
}
