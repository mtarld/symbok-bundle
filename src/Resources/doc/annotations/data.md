# Data annotation
Data constructor annotation will tell the compiler to create a constructor which sets each attribute of current class. It will also tell the compiler to create getters and setters for each attributes

## Type
Class annotation

## Options
- `constructorNullable`: Boolean. Represents if constructor parameters will be nullable by default. See [Constructor parameters nullable priorities](../priorities.md#constructor-parameter-nullable)
- `nullable`: Boolean. Represents if getters/setters will return/use nullable values/parameters by default. See [Getters/Setters nullable priorities](../priorities.md#getterssetters-nullable)
- `fluentSetters`: Boolean. Represents if setters have to return self class instance. See [Fluent setters priorities](../priorities.md#fluent-setters)
  side when Doctrine is detected. See

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
    
    public function __construct(int $id, string $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }
    
    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
```
