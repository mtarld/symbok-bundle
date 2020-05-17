<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;
use Mtarld\SymbokBundle\Annotation\Getter;

/**
 * Description.
 *
 * @author Mathias Arlaud
 * @Symbok\ToString(properties={"id"})
 * @Symbok\Data
 * @ORM\Entity
 *
 * @method int|null getNbCall
 */
class Product3
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Getter()
     */
    private $id;
}
