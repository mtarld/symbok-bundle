# Nullable annotation
Nullable annotation will tell the compiler that current property could be considered as nullable.

## Type
Property annotation

## Options
- `nullable`: Boolean. Default `true`. Represents if property should be nullable. See [Getters/Setters nullable priorities](../priorities.md#getterssetters-nullable)

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
     * @Nullable(nullable=true)
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

    /**
     * Retrieves id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
```
