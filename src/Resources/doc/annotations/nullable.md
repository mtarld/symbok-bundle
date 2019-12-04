# Nullable annotation
Tells the compiler that current property should be considered as nullable.

## Type
Property annotation

## Options
- `nullable`: Boolean. Default `true`. Represents if property should be
 nullable.
 See [Nullable priorities](../priorities.md)

## Example
### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Nullable;

class Product
{
    /**
     * @var int
     * @Getter
     * @Nullable(nullable=false)
     */
    private $id;
}
```

### Compiled file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Nullable;

class Product
{
    /**
     * @var int
     */
    private $id;

    public function getId(): int
    {
        return $this->id;
    }
}
```
