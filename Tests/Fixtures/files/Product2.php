<?php

namespace App\Entity;

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
     * @Symbok\Setter(noAdd=true)
     */
    private $prices;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @Symbok\Getter()
     */
    private $category;

    public function addPrice($price)
    {
        $this->prices->add($price);
    }
}
