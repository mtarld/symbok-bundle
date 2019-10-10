<?php

namespace Mtarld\SymbokBundle\Behavior;

use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\Model\SymbokClass;

class ClassBehavior
{
    public function requireAllArgsConstructor(SymbokClass $class): bool
    {
        return $class->getAnnotation(AllArgsConstructor::class) instanceof AllArgsConstructor
            || $class->getAnnotation(Data::class) instanceof Data
        ;
    }

    public function requireToString(SymbokClass $class): bool
    {
        return $class->getAnnotation(ToString::class) instanceof ToString;
    }
}
