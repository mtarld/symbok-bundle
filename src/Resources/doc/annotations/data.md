# Data annotation
Data annotation tells the compiler to create a constructor which sets each
property of current class.
Also tells the compiler to create getters and setters for each properties

## Type
Class annotation

## Options
- `constructorNullable`: Boolean. Represents if constructor parameters will be
 nullable by default.
 See [Constructor parameters nullable priorities](../priorities.md)
- `nullable`: Boolean. Represents if getters/setters will return/use nullable
 values/parameters by default.
 See [Getters/Setters nullable priorities](../priorities.md)
- `fluent`: Boolean. Represents if setters have to return self class instance.
See [Fluent setters priorities](../priorities.md)
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

use Mtarld\SymbokBundle\Annotation\Data;

/**
 * @Data
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

use Mtarld\SymbokBundle\Annotation\Data;

/**
 * @Data
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
    
    public function getId() : ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
```
