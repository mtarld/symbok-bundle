<?php

namespace Mtarld\SymbokBundle\Tests\Replacer;

use App\Entity\Product1;
use App\Entity\Product2;
use App\Entity\Product3;
use App\Model\Product4;
use Mtarld\SymbokBundle\Replacer\RuntimeClassReplacer;
use Mtarld\SymbokBundle\Tests\KernelTestCase;

/**
 * @group functional
 * @group replacer
 */
class RuntimeClassReplacerFunctionalTest extends KernelTestCase
{
    public function testClassIsReplacedWithAutoload(): void
    {
        /** @var RuntimeClassReplacer $replacer */
        $replacer = static::$container->get('symbok.replacer.runtime');

        $this->assertSame(
            '<?php

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
    public function setId(?int $id) : self
    {
        $this->id = $id;
        return $this;
    }
    public function getName() : ?string
    {
        return $this->name;
    }
    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }
    public function getImage()
    {
        return $this->image;
    }
}',
            $replacer->replace(Product1::class)
        );

        $this->assertSame(
            '<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;
/**
 */
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
    public function __construct()
    {
        $this->prices = new Doctrine\Common\Collections\ArrayCollection();
    }
    public function getPrices() : ?\Doctrine\Common\Collections\Collection
    {
        return $this->prices;
    }
    public function removePrice(App\Entity\Price $price) : self
    {
        if ($this->prices->contains($price)) {
            $this->prices->removeElement($price);
            $price->removeProducts($this);
        }
        return $this;
    }
    public function getCategory() : ?\App\Entity\Category
    {
        return $this->category;
    }
    public function setCategory(?\App\Entity\Category $category) : void
    {
        $this->category = $category;
    }
}',
            $replacer->replace(Product2::class)
        );

        $this->assertSame(
            '<?php

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
 * @method int|null getNbCall()
 */
class Product3
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Getter()
     */
    private $id;
    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }
    public function __toString() : string
    {
        return (string) (\'Product3: \' . $this->id);
    }
    public function getId() : ?int
    {
        return $this->id;
    }
    public function setId(?int $id) : self
    {
        $this->id = $id;
        return $this;
    }
}',
            $replacer->replace(Product3::class)
        );

        if (PHP_VERSION_ID >= 70400) {
            $this->assertSame(
                '<?php

namespace App\Model;

use App\Entity\Product1;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Setter;
/**
 */
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
    /**
     * @var string[]
     * @Setter(remove=false)
     */
    private array $names;
    public function getId() : ?int
    {
        return $this->id;
    }
    public function getRelated() : ?\App\Entity\Product1
    {
        return $this->related;
    }
    public function setNames(?array $names) : self
    {
        $this->names = $names;
        return $this;
    }
    public function addName(string $name) : self
    {
        $this->names[] = $name;
        return $this;
    }
}',
                $replacer->replace(Product4::class)
            );
        }
    }
}
