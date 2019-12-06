# Doctrine properties handling
## Doctrine `Column` annotation
Symbok is parsing `Doctrine\ORM\Mapping\Column` and `Doctrine\ORM\Mapping\JoinColumn` annotations.

After parsed them, it will automatically set **property type** and
 **nullable status** (if not overriden - see [priorities](priorities.md)).

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
     * @Setter(fluent=false)
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
     * @Setter(fluent=false)
     */
    private $id;

    public function setId(?int $id) : void
    {
        $this->id = $id;
    }
}
```

## Doctrine entity relations
Symbok is also parsing Doctrine entity relation annotations:
- `Doctrine\ORM\Mapping\OneToOne`
- `Doctrine\ORM\Mapping\OneToMany`
- `Doctrine\ORM\Mapping\ManyToOne`
- `Doctrine\ORM\Mapping\ManyToMany`

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @Setter
     */
    private $category;

    public function setCategory(?\App\Entity\Category $category): self
    {
        $this->category = $category;
        $category->addProduct($this);
        
        return $this;
    }
}
```
### \*ToMany relation
For \*ToMany relations, Symbok reads `Doctrine\ORM\Mapping\JoinColumn` annotation to discover property **nullable status**.

It will as well add `add` and `remove` methods if setter is needed.

You can prevent to generate these methods using `add` and `remove`
paremeters of [Setter annotation](annotations/setter.md) and [Data annotation](annotations/data.md)

Moreover, if class doesn't contain `__construct` function or isn't annotated
with [AllArgsConstructor annotation](annotations/allArgsConstructor.md), a
constructor function will be added in order to initialize the collection property.

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
     * @Setter(fluent=false)
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
     * @Setter(fluent=false)
     */
    private $prices;

    public function __construct()
    {
        $this->prices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setPrices(?\Doctrine\Common\Collections\Collection $prices): void
    {
        $this->prices = $prices;
        $prices->setProduct($this)
    }

    public function addPrice(\App\Entity\Price $price): void
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
            $price->addProduct($this);
        }
    }

    public function removePrice(\App\Entity\Price $price): void
    {
        if ($this->prices->contains($price)) {
            $this->prices->removeElement($price);
            $price->removeProduct($this);
        }
    }
}
```

## Other side updating
As you may have seen above, Symbok updates the other side of doctrine
relation so that your models are still valid in memory.

If needed, you can easily prevent this behavior by using `updateOtherSide`
parameter of [Setter annotation](annotations/setter.md) and [Data annotation](annotations/data.md)

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
     * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
     * @Setter(fluent=false, updateOtherSide=false)
     */
    private $prices;
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
     * @Setter(fluent=false, updateOtherSide=false)
     */
    private $prices;

    public function __construct()
    {
        $this->prices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setPrices(?\Doctrine\Common\Collections\Collection $prices): void
    {
        $this->prices = $prices;
    }

    public function addPrice(\App\Entity\Price $price): void
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
        }
    }

    public function removePrice(\App\Entity\Price $price): void
    {
        if ($this->prices->contains($price)) {
            $this->prices->removeElement($price);
        }
    }
}
```

### Other side property naming
In order to find other side property name, Symbok will first look at the
`mappedBy`/`inversedBy` attribute of doctrine annotation. If nothing is found,
then it will fallback to the current class name.

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
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="soldProduct")
     * @Setter
     */
    private $seller;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Picture")
     * @Setter
     */
    private $picture;
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
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="soldProduct")
     * @Setter
     */
    private $seller;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Photo")
     * @Setter
     */
    private $picture;

    public function setSeller(?\App\Entity\User $seller): self
    {
        $this->seller = $seller;
        $seller->setSoldProduct($this);
        
        return $this;
    }

    public function setPicture(?\App\Entity\Photo $picture): self
    {
        $this->picture = $picture;
        $seller->setProduct($this);
        
        return $this;
    }
}
```
