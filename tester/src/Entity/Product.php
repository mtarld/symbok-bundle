<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Nullable;
use Mtarld\SymbokBundle\Annotation\Setter;
use Mtarld\SymbokBundle\Annotation\Data;
use Mtarld\SymbokBundle\Annotation\ToString;
use Symfony\Component\PropertyInfo;

/**
 * @ToString(properties={"id", "name"})
 * @method string __toString()
 * @method int|null getId()
 * @method void setId(int|null $id)
 * @method string getName()
 * @method void setName(string $name)
 */
class Product
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Getter()
     * @Setter()
     */
    private $id;

    /**
     * @var string
     * @Getter()
     * @Setter()
     */
    private $name;

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
