<?php

namespace Mtarld\SymbokBundle\Cache;

interface CacheInterface
{
    public function exists(string $className): bool;

    public function load(string $className): void;

    public function write(string $className, string $content): void;
}
