# Getter annotation
Tells the compiler to create a getter method for current property.

## Type
Property annotation

## Options
- `nullable`: Boolean. Represents if getter returned value could be nullable.
See [Getters' return nullable priorities](../priorities.md).
- `hasPrefix`: Boolean. Default `false`. When getting a boolean, `getXXX` will
  be transformed to `isXXX`. `hasPrefix` represents if `isXXX` should be
  `hasXXX` instead.

## Example
### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Getter;

class Product
{
    /**
     * @var int
     * @Getter
     */
    private $id;
}
```

### Compiled file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Getter;

class Product
{
    /**
     * @var int
     * @Getter
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
```
