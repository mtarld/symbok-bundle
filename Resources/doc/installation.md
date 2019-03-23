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
        # If setters are fluent by default (default false)
        fluent_setters: ~

        nullable:
            # If getters and setters use/return nullable parametes/values (default false)
            getter_setter: ~

            # If constructor uses nullable parameters (default true)
            constructor: ~
```

Then, you should be all set !
