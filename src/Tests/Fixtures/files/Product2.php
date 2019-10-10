<?php

namespace Mtarld\SymbokBundle\Tests\Fixtures\files;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;

class Product2
{
    /**
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     * @Symbok\Getter(nullable=true)
     * @Symbok\Setter(add=false)
     */
    private $prices;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @Symbok\Getter()
     * @Symbok\Setter(fluent=false)
     */
    private $category;

    public function addPrice($price)
    {
        $this->prices->add($price);
    }
}
