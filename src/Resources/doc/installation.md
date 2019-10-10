# Symbok installation instructions
## Installation
You can easily install Symbok by composer
```
$ composer require mtarld/symbok-bundle
```

## Configuration
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
        getter: ~
            # If getters' return type is nullable by default (default true)
            nullable: ~

        setter: ~
            # If setters are fluent by default (default true)
            fluent: ~

            # If setters' parameters are nullable by default (default true)
            nullable: ~

        constructor:
            # If constructor uses nullable parameters (default true)
            nullable: ~
```

Then, you should be all set !
