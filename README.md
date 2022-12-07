# :warning: Status: Archived

With PHP 8.1 and the emergence of public readonly properties, this bundle
repository has been archived and is no longer maintained.

If you really need to  generate getters and setters generation, you can have a
look at [lombok-php](https://github.com/MarcinOrlowski/lombok-php).

# Symbok Annotation Bundle

![Packagist](https://img.shields.io/packagist/v/mtarld/symbok-bundle.svg)
![GitHub](https://img.shields.io/github/license/mtarld/symbok-bundle.svg)
[![Actions Status](https://github.com/mtarld/symbok-bundle/workflows/CI/badge.svg)](https://github.com/mtarld/symbok-bundle/actions)

Runtime code generator bundle for Symfony.

- Detects classes that are using Symbok annotations, generates related methods 
and loads generated class instead of the original one.
- Stores generated classes in Symfony cache so that Symbok compiles them just once.
- Reads basic Doctrine annotations to handle property's type, nullable status
  and entity relation.

Initially inspired by [Plumbok](https://github.com/plumbok/plumbok).

Compatible with Symfony 4 and 5

## Symbok ?
> :wave: Bye bye endless PHP classes !

Symbok provides annotations in order to generate on the fly predictable and repetitive methods.

Available annotations are:
  - AllArgsConstructor
  - Data
  - ToString
  - Getter
  - Setter
  - Nullable

Symbok also parses doctrine properties annotations such as `Column`,
`JoinColumn`, `OneToOne`, `OneToMany`, `ManyToOne`, `ManyToMany` in order to 
automatically discover property type, nullable status and adapt generated methods.

You'll be able to find more precise information on Symbok Bundle in the [documentation](src/Resources/doc/index.md)

## Getting started
### Installation
You can easily install Symbok by composer
```
$ composer require mtarld/symbok-bundle
```
Then, bundle should be registered. Just verify that `config\bundles.php` is containing :
```php
Mtarld\SymbokBundle\SymbokBundle::class => ['all' => true]
```

### Configuration
Once Symbok is installed, you should configure it to fit your needs. 

To do so, edit `config/packages/symbok.yaml`
```yaml
# config/packages/symbok.yaml

symbok:
    # Namespaces that you wanna be processed
    namespaces:
        - 'App\Entity'
        - 'App\Model'
        
    defaults:
        getter: ~
            # If getters are nullable by default (default true)
            nullable: ~

        setter: ~
            # If setters are fluent by default (default true)
            fluent: ~

            # If setters are nullable by default (default true)
            nullable: ~

            # If setters should update other side when relation is detected (default true)
            updateOtherSide: ~

        constructor:
            # If constructor uses nullable parameters (default true)
            nullable: ~
```
And you're ready to go ! :rocket:

## Basic example
Register your namespace in config file
```yaml
# config/packages/symbok.yaml

symbok:
    namespaces:
      - 'App\Entity'
```
Then edit your class by adding annotations
```php
<?php

// src/Entity/Product.php

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
}
```

## Provided commands
### Updating original files with `symbok:update:classes`
``` bash
$ php bin/console symbok:update:classes
```

When running this command, original classes' docblock will be updated with
good `@method` tags so that IDEs will be able to know that new methods exist.

For instance, the class:
```php
<?php

// src/Entity/Product.php

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

Will be rewritten as:
```php
<?php

// src/Entity/Product.php

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

### Previewing results with `symbok:preview`

``` bash
$ php bin/console symbok:preview [-s|--compilationStrategy COMPILATIONSTRATEGY] <class path>
```

By using that command, you will be able preview Symbok compilation results
directly in your CLI.

Compilation strategy represents which compilation will be applied on target
class. It could be either:
- `runtime` to preview PHP code that will be executed at runtime
- `saved` to preview PHP code that will be written when using
  `symbok:update:classes` command

## Documentation
A detailed documentation is available [here](src/Resources/doc/index.md)

## Contributing
Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

After writing your fix/feature, you can run following commands to make sure that everyting is still ok.

```bash
# Install dev dependencies
$ composer install

# Running tests locally
$ make test

```

## Authors
 - Mathias Arlaud - [mtarld](https://github.com/mtarld) - <mathias(dot)arlaud@gmail(dot)com>
