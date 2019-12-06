# Setter annotation
Setter annotation will tell the compiler to create a setter method for current property.

## Type
Property annotation

## Options
- `nullable`: Boolean. Represents if setter parameter could be nullable.
See [Setters's parameter nullable priorities](../priorities.md).
- `fluent`: Boolean. Represents if setter method returns self class instance.
See [Fluent setter priorities](../priorities.md).
- `updateOtherSide`: Boolean. Represents if setters/adders/removers have to
  update other side of relation in case of doctrine relation.
- `add`: Boolean. Default `true`. Represents if adders have to be added (in case
  of collection property)
- `remove`: Boolean. Default `true`. Represents if removers have to be added (in
  case of collection property)

## Example
### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;

class Product
{
    /**
     * @var int
     * @Setter(fluent=false)
     */
    private $id;
}
```

### Compiled file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;

class Product
{
    /**
     * @var int
     * @Setter(fluent=false)
     */
    private $id;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }
}
```
## Fluent setters
In case of fluent setter asked, self instance is returned
### Example
#### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;

class Product
{
    /**
     * @var int
     * @Setter(fluent=true)
     */
    private $id;
}
```

#### Compiled file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;

class Product
{
    /**
     * @var int
     * @Setter(fluent=true)
     */
    private $id;

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
}
```

## Collection property
When the property is representing a collection (either an array or a
doctrine collection relation), `add` and `remove` method will be added.

### Array example
#### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;

class Product
{
    /**
     * @var int[]
     * @Setter
     */
    private $prices;
}
```

#### Compiled file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Setter;

class Product
{
    /**
     * @var int[]
     * @Setter
     */
    private $prices;

    public function setPrices(?array $prices) : self
    {
        $this->prices = $prices;
        return $this;
    }

    public function addPrice(int $price) : self
    {
        $this->prices[] = $price;
        return $this;
    }

    public function removePrice(int $price) : self
    {
        $key = array_search($price, $this->prices, true);
        if (false !== $key) {
            unset($this->prices[$key]);
        }
        return $this;
    }
}
```

### Doctrine relation example
See [Doctrine *ToMany relations](../doctrine.md) for more details
