<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Annotation\ToString;

/**
 * @AllArgsConstructor(nullable=false)
 * @ToString(properties={"id", "qty"})
 */
class Product3
{
    /**
     * @ORM\Column(type="integer")
     * @Getter
     */
    private $id;

    /**
     * @var ?int
     * @Getter
     */
    private $qty;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     * @Setter(fluent=true)
     */
    private $prices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="products")
     * @ORM\JoinColumn(nullable=false)
     * @Setter(fluent=true)
     */
    private $images;
}
