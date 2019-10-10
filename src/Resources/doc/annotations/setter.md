# Setter annotation
Setter annotation will tell the compiler to create a setter method for current property.

## Type
Property annotation

## Options
- `nullable`: Boolean. Represents if setter parameter could be nullable. See [Getters/Setters nullable priorities](../priorities.md#getterssetters-nullable).
- `fluent`: Boolean. Represents if setter method returns self class instance. See [Fluent setter priorities](../priorities.md#fluent-setters).
- `add`: Boolean. Represents if add method is not wanted (in case of [Doctrine entity relations](../doctrine.md#doctrine-entity-relations) or array).
- `remove`: Boolean. Represents if remove method is not wanted (in case of [Doctrine entity relations](../doctrine.md#doctrine-entity-relations) or array).

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

    public function setId(int $id): void
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

## Doctrine entity relation
In case of the property is representing a doctrine entity relation, you may have a look at [Doctrine entity relations](../doctrine.md).
Indeed, `add` and `remove` methods may be written in case of \*ToMany relation.
