<?php

namespace Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;
use Mtarld\SymbokBundle\Annotation\Getter;

/**
 * Description.
 *
 * @author Mathias Arlaud
 * @Symbok\ToString(properties={"id", "name"})
 * @Symbok\Data
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
