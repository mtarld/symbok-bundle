# Symbok Annotation Bundle - Documentation
## Symbok ?
> :wave: Bye bye endless PHP classes !

Symbok is a runtime code generator bundle for Symfony.

It detects classes that are using Symbok annotations, generates related methods 
and loads generated class instead of the original one.
Generated classes will be stored in Symfony cache so that Symbok compiles them just once.

## Installation
Installation instructions are available in the [installation documentation](installation.md)

## Main features
### Annotations
Symbok provides annotations in order to generate on the fly predictable and repetitive methods:

Class annotations
  - [AllArgsConstructor](annotations/allArgsConstructor.md)
  - [Data](annotations/data.md)
  - [ToString](annotations/toString.md)

Property annotations
  - [Getter](annotations/getter.md)
  - [Setter](annotations/setter.md)
  - [Nullable](annotations/nullable.md)

### Property type guessing
Using docblock, Symbok will guess the php7 property type. 

You'll able to find out how the type is guessed by reading the [compiler priorities documentation](priorities.md)

### Doctrine handling
Symbok also parses doctrine properties annotations such as `Column`, `JoinColumn`, `OneToOne`, `OneToMany`, `ManyToOne`, `ManyToMany` in order to automatically discover property type and nullable status.

It will as well write `add` and `remove` methods if property is targeting a
collection and if a setter is needed.

Moreover, as Symfony's maker bundle does, Symbok will handle relation's other
side for `set`, `add` and `remove` methods.

More information in the [doctrine documentation](doctrine.md)

## Basic example
Register your namespace in config file
```yaml
symbok:
    namespaces:
      - 'App\Entity'
```
Then edit your class by adding annotations
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Getter;

class Product
{
    /**
     * @Getter
     */
    private int $id;
}
```
Then, the class will be executed as the following:
```php
<?php

namespace App\Entity;

use Mtarld\SymbokBundle\Annotation\Getter;

class Product
{
    /**
     * @Getter
     */
    private int $id;
    
    public function getId(): ?int
    {
        return $this->id;
    }
```

## Commands
Symbok comes with two commands:
- `symbok:update:classes` which will add `@method` tags to original classes
- `symbok:preview` which allows you to preview compilation results

More informations in the [commands documentation](commands.md)

## Performances
If you're wandering about Symbok's impact on overall preformances, here is a
little benchmark representing a **Symfony command execution** in **production
environment** that is **printing the id of a class**.

|                   | Without Symbok | With Symbok  | Δ      |
|-------------------|----------------|--------------|--------|
| 0 class involved  | ~29ms          | ~32.5ms      | ~3.5ms |
| 1 class involved  | ~29.4ms        | ~34.2ms      | ~4.8ms |
| 2 class involved  | ~30.2ms        | ~34.9ms      | ~4.7ms |
| 5 class involved  | ~31.1ms        | ~35.1ms      | ~4.0ms |
| 10 class involved | ~32.4ms        | ~36.5ms      | ~4.1ms |
