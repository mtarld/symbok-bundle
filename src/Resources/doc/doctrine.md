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
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @Setter
     */
    private $category;

    public function setCategory(\App\Entity\Category $category): self
    {
        $this->category = $category;
        $category->addProduct($this);
        
        return $this;
    }
}
```
### \*ToMany relation
For \*ToMany relations, Symbok reads `Doctrine\ORM\Mapping\JoinColumn` annotation to discover property nullable status.

Symbok automatically add the useful methods: `add` and `remove` if setter is
needed.
You can prevent to generate these methods using `add` and `remove`
paremeters of [Setter annotation](annotations/setter.md) and [Data annotation](annotations/data.md)
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
     * @JoinColumn(nullable=true)
     * @Setter(fluent=false)
     */
    private $prices;

    public function setPrices(?\Doctrine\Common\Collections\Collection $prices): void
    {
        $prices->setProduct($this)
        $this->prices = $prices;
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
     * @Setter(fluent=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="products")
     * @Setter(fluent=false)
     */
    private $event;
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
     * @Setter(fluent=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="products")
     * @Setter(fluent=false)
     */
    private $event;

    public function setCategory(\App\Entity\Category $category): void
    {
        $this->category = $category;
        $category->addProduct($this);
    }

    public function setEvent(\App\Entity\Event $event): void
    {
        $this->event = $event;
    }
}
```
