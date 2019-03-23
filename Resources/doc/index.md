# Symbok Annotation Bundle - Documentation
## Symbok ?
Symbok is a runtime code generator bundle for Symfony.

It uses an additional autoloader to detect classes that are using Symbok annotations. Then it generates related methods and load generated class instead of the original one.
Finally, the generated class is stored in Symfony cache so that Symbok compiles it just once.

This Symfony bundle was initially inspired by [Plumbok](https://github.com/plumbok/plumbok).

## Installation
Installation instructions are available [here](installation.md)

## Main features
### Annotations
Symbok provides basic annotations, such as

Class annotations
  - [AllArgsConstructor](annotations/allArgsConstructor.md)
  - [Data](annotations/data.md)
  - [ToString](annotations/toString.md)

Properties annotations
  - [Getter](annotations/getter.md)
  - [Setter](annotations/setter.md)
  - [Nullable](annotations/nullable.md)

### Doctrine properties handling
Symbok also parses doctrine properties annotations such as `Column`, `JoinColumn`, `OneToOne`, `OneToMany`, `ManyToOne`, `ManyToMany` in order to automatically discover property type and nullable status.
Symbok will as well write `add` and `remove` methods if property is targeting a collection and setter is needed.

More information in the [doctrine documentation](doctrine.md)

### Property type guessing
Using docblock, Symbok can guess the php7 property type. 

You'll able to find out how the type is guessed by reading [compiler priorities documentation](priorities.md)

## Basic example
Register desired namespace in config file
```yaml
symbok:
    namespaces:
      - 'App\Entity'
```
Then edit your file by adding annotations
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
The class will compiled and interpreted as the following
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
    
    /**
     * Retrieves id
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
```
Moreover, the original class docblock will be updated with `@method` tags to permit IDEs to know that getter method exists
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Getter;

/**
 * @method int getId()
 */
class Product
{
    /**
     * @var int
     * @Getter
     */
    private $id;
}
```
