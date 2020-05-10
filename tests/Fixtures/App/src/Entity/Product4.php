<?php

namespace Mtarld\SymbokBundle\Tests\Fixtures\files;

use Mtarld\SymbokBundle\Annotation\Getter;

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
}
