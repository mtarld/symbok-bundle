<?php

namespace Mtarld\SymbokBundle\Repository;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Nullable;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Annotation\ToString;
use ReflectionClass;

class AnnotationRepository
{
    public const SYMBOK = [
        AllArgsConstructor::class,
        Data::class,
        Getter::class,
        Nullable::class,
        Setter::class,
        ToString::class,
    ];

    public const DOCTRINE = [
        OneToOne::class,
        OneToMany::class,
        ManyToOne::class,
        ManyToMany::class,
        Column::class,
        JoinColumn::class,
    ];

    public function findAll(): array
    {
        return array_merge(self::SYMBOK, self::DOCTRINE);
    }

    public function findNamespaces(): array
    {
        return [
            '\\'.(new ReflectionClass(self::SYMBOK[0]))->getNamespaceName(),
            '\\'.(new ReflectionClass(self::DOCTRINE[0]))->getNamespaceName(),
        ];
    }
}
