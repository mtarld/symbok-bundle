# Getting started with Symbok
## Installation
You can easily install Symbok by composer
```
$ composer require mtarld/symbok-bundle
```

## Configuration
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
