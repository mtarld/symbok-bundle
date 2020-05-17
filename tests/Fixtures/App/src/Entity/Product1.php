<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Setter;

/**
 * Description.
 *
 * @Symbok\ToString(properties={"id", "name"})
 * @Symbok\Data(fluent=true, nullable=true, constructorNullable=false)
 */
class Product1
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Getter()
     */
    private $id;

    /**
     * @var string
     * @Symbok\Nullable()
     * @Setter(fluent=true, nullable=false)
     */
    private $name;

    private $image;

    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
}
