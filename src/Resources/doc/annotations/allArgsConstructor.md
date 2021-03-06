# AllArgsConstructor
Tells the compiler to create a constructor which sets each attribute of current class.

## Type
Class annotation

## Options
- `nullable`: Boolean. Represents if constructor parameters will be nullable by default. 
  See [Constructor parameters nullable priorities](../priorities.md)

## Example
### Original file
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;

/**
 * @AllArgsConstructor
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

use Mtarld\SymbokBundle\Annotation\AllArgsConstructor;

/**
 * @AllArgsConstructor
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
    
    public function __construct(?int $id = null, ?string $name = null)
    {
        $this->id   = $id;
        $this->name = $name;
    }
}
```
