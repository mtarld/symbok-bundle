<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Nullable;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Annotation\Data;
use Symfony\Component\PropertyInfo;

/**
 * @method \Symfony\Component\PropertyInfo\PropertyInfoCacheExtractor|null getId()
 */
class Product
{
    /**
     * @var PropertyInfo\PropertyInfoCacheExtractor
     * @ORM\Column(type="simple_array", nullable=true)
     * @Getter()
     */
    private $id = 0;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     */
    private $prices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Purchase", mappedBy="product")
     */
    private $purchases;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     */
    private $category;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Description", mappedBy="product")
     */
    private $description;
}
