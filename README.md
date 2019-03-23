# Symbok Annotation Bundle
Runtime code generator bundle for Symfony.

Uses an additional autoloader to detect classes that are using Symbok annotations. Then Symbok generates related methods and load generated class instead of the original one.
Generated class is stored in Symfony cache so that Symbok compiles it just once.
Also reads basic Doctrine annotations to handle entity relations as well.

Initially inspired by [Plumbok](https://github.com/plumbok/plumbok).

Compatible with Symfony3 and Symfony4

![Packagist](https://img.shields.io/packagist/v/mtarld/symbok-bundle.svg?style=flat-square)
![GitHub](https://img.shields.io/github/license/mtarld/symbok-bundle.svg?style=flat-square)
![Travis (.org)](https://img.shields.io/travis/mtarld/symbok-bundle.svg?style=flat-square)

## Features
Symbok provides basic annotations, such as
- AllArgsConstructor
- Data
- ToString
- Getter
- Setter
- Nullable

## Getting started
### Installation
You can easily install Symbok by composer
```
$ composer require mtarld/symbok-bundle
```
If you are using Symfony 3, you'll have to load bundle by modifying `app/AppKernel.php` to add the following :
```php
public function registerBundles()
{
    $bundles = [
        // ...
        new Mtarld\SymbokBundle\SymbokBundle(),
    ];

    // ...

    return $bundles;
}
```
If you are using Symfony 4, bundle should be already registered. Just verify that `config\bundles.php` is containing :
```php
Mtarld\SymbokBundle\SymbokBundle::class => ['all' => true]
```
#### Symfony 3

### Configuration
Once Symbok is installed, you must configure it to fit your needs. 
To do that, just edit `config/packages/symbok.yaml` for Symfony flex, or `app/config/config.yml` for Symfony 3 and configure the following
```yaml
symbok:
    # Namespaces that you wanna be processed
    namespaces:
        - 'App\Entity'
        - 'App\Model'
        
    # Cache activation (useful to disable it when developing)
    cache: ~

    defaults:
        # If setters are fluent by default (default false)
        fluent_setters: ~

        nullable:
            # If getters and setters use/return nullable parametes/values (default false)
            getter_setter: ~

            # If constructor uses nullable parameters (default true)
            constructor: ~
```

### Basic example
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
     */
    private $id;
    
    public function getId(): int
    {
        return $this->id;
    }
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

## Documentation
A more detailed documentation is available [here](Resources/doc/index.md)

## Contributing
Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

After writing your fix/feature, you can run following commands to make sure that everyting is still ok.

```bash
# Install dev dependencies
$ composer install

# Running tests locally
$ ./vendor/bin/phpunit

# Cleaning your code (in case of)
$ ./vendor/bin/php-cs-fixer
```

## Authors
- Mathias Arlaud - @mtarld - <mathias(dot)arlaud@gmail(dot)com>
