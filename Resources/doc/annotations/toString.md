# ToString
ToString annotation will tell the compiler to create a method that converts current class to a simple string.
It'll simply concatenate class and specified attributes (see example below).

## Type
Class annotation

## Options
- `properties`: Array. Represents which attributes have to be printed by toString method.

## Example
### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\ToString;

/**
 * @ToString(properties={"id", "name"})
 */
class Product
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;
}
```

### Compiled file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\ToString;

/**
 * @ToString(properties={"id", "name"})
 */
class Product
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;
    
    /**
     * Product toString.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) ('Product: ' . ($this->id . (', ' . $this->name)));
    }
}
```
