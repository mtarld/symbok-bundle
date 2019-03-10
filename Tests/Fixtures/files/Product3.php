<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;
use Mtarld\SymbokBundle\Annotation\Getter;

/**
 * @Symbok\AllArgsConstructor(nullable=false)
 */
class Product3
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Getter()
     */
    private $id;
}
