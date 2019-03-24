# Doctrine properties handling
## Doctrine's column annotation
Symbok is parsing `Doctrine\ORM\Mapping\Column` annotations.
After parsed it, Symbok can automatically discover property type and nullable status based on `Column` annotation (if not overriden - see [priorities](priorities.md#getterssetters-nullable)).

### Example
#### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;
use Doctrine\ORM\Mapping as ORM;

class Product
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Setter()
     */
    private $id;
}
```

#### Compiled file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;
use Doctrine\ORM\Mapping as ORM;

class Product
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Setter()
     */
    private $id;

    /**
     * Sets id
     *
     * @param int|null $id
     * @return void
     */
    public function setId(?int $id)
    {
        $this->id = $id;
    }
}
```

## Doctrine entity relations
Symbok is also parsing Doctrine entity relations annotations:
- `Doctrine\ORM\Mapping\OneToOne`
- `Doctrine\ORM\Mapping\OneToMany`
- `Doctrine\ORM\Mapping\ManyToOne`
- `Doctrine\ORM\Mapping\ManyToMany`

By reading these annotations, Symbok can discover property type and nullable status according to read relation.
### \*ToOne relation
#### Example
##### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;
use Doctrine\ORM\Mapping as ORM;

class Product
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @Setter
     */
    private $category;
}
```

##### Compiled file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;
use Doctrine\ORM\Mapping as ORM;

class Product
{
    /**
     * @var \App\Entity\Category
     */
    private $category;

    public function setCategory(\App\Entity\Category $category): void
    {
        $this->category = $category;
    }
}
```
### \*ToMany relation
For \*ToMany relations, Symbok reads `Doctrine\ORM\Mapping\JoinColumn` annotation to discover property nullable status.
Moreover, for \*ToMany relations, Symbok automatically add the useful methods: `add` and `remove` if setter is needed. You can prevent to generate these methods using `noAdd` and `noRemove` paremeters of [Setter annotation](annotations/setter.md)
#### Example
##### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;
use Doctrine\ORM\Mapping as ORM;

class Product
{
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
     * @JoinColumn(nullable=true)
     * @Setter
     */
    private $prices;
}
```

##### Compiled file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;
use Doctrine\ORM\Mapping as ORM;

class Product
{
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     * @Setter
     */
    private $prices;

    /**
     * Sets prices
     *
     * @param \Doctrine\Common\Collections\Collection|null $prices
     * @return void
     */
    public function setPrices(?\Doctrine\Common\Collections\Collection $prices)
    {
        $prices->setProduct($this)
        $this->prices = $prices;
    }

    /**
     * Adds price to prices
     *
     * @param \App\Entity\Price $price
     * @return void
     */
    public function addPrice(\App\Entity\Price $price)
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
            $price->addProduct($this);
        }
    }

    /**
     * Removes price from prices
     *
     * @param \App\Entity\Price $price
     * @return void
     */
    public function removePrice(\App\Entity\Price $price)
    {
        if ($this->prices->contains($price)) {
            $this->prices->removeElement($price);
            $price->removeProduct($this);
        }
    }
}
```
