<?php

namespace Mtarld\SymbokBundle\Tests\Replacer;

use Mtarld\SymbokBundle\Replacer\SavedClassReplacer;
use Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity\Product2;
use Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity\Product3;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group functional
 * @group replacer
 */
class SavedClassReplacerFunctionalTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel();
    }

    /**
     * @testdox Saved class is replaced with good content
     */
    public function testSavedClassReplacementContent(): void
    {
        /** @var SavedClassReplacer $replacer */
        $replacer = static::$container->get('symbok.replacer.saved');

        $this->assertSame(
            '<?php

namespace Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;
use Mtarld\SymbokBundle\Annotation\Getter;

/**
 * Description.
 *
 * @author Mathias Arlaud
 * @Symbok\ToString (properties={"id", "name"})
 * @Symbok\Data
 * @method int|null getNbCall()
 * @method mixed __construct(?int $id)
 * @method string __toString()
 * @method int|null getId()
 * @method self setId(?int $id)
 */
class Product3
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Getter()
     */
    private $id;
}
',
            $replacer->replace(Product3::class)
        );

        $this->assertSame(
            '<?php

namespace Mtarld\SymbokBundle\Tests\Fixtures\App\src\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;

/**
 * @method mixed __construct()
 * @method \Doctrine\Common\Collections\Collection|null getPrices()
 * @method self removePrice(\App\Entity\Price $price)
 * @method \App\Entity\Category|null getCategory()
 * @method void setCategory(?\App\Entity\Category $category)
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
}
',
            $replacer->replace(Product2::class)
        );
    }
}
