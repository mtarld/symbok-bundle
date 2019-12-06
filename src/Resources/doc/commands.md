# Commands
## Updating original files with `symbok:update:classes`
``` bash
$ php bin/console symbok:update:classes
```

When running `symbok:update:classes`, original classes' docblock will be updated with
good `@method` tags so that IDEs will be able to know that new methods exist.

### Example
Let's say that `src/Entity/Product.php` contains the following:
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

Then running:
```
$ php bin/console symbok:update:classes
```

will replace content of `src/Entity/Product.php` with:
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

## Previewing results with `symbok:preview`
``` bash
$ php bin/console symbok:preview [-s|--compilationStrategy COMPILATIONSTRATEGY] <path>
```

By using `symbok:preview`, you will be able preview Symbok compilation results
directly in your CLI.

### Parameters
#### `path`
This required parameter represents the path of class that you wanna be previewed.

#### `compilationStrategy`
This optional parameter represents which kind of compilation will be applied on the class. 

It could be either:
- `runtime` to preview PHP code that will be executed at runtime
- `saved` to preview PHP code that will be written when using
  `symbok:update:classes` command

### Example
Let's say that `src/Entity/Product.php` contains the following:
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

Then the output of:
```
$ php bin/console symbok:preview --compilationStrategy runtime src/Entity/Product.php
```

Will be:
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
    
    public function getId(): ?int
    {
        return $this->id;
    }
}
```

And the output of:
```
$ php bin/console symbok:preview --compilationStrategy saved src/Entity/Product.php
```

Will be:
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
