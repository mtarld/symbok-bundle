<?php

namespace Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity;

use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Setter;

class Product4
{
    /**
     * @Getter()
     */
    private ?int $id;

    /**
     * @Getter()
     */
    private Product1 $related;

    /**
     * @var string[]
     * @Setter(remove=false)
     */
    private array $names;
}
