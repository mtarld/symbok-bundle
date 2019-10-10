# Getter annotation
Getter annotation will tell the compiler to create a getter method for current property.

## Type
Property annotation

## Options
- `nullable`: Boolean. Represents if getter returned value could be nullable.

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

    public function getId(): int
    {
        return $this->id;
    }
}
```
