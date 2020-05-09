<?php

namespace Mtarld\SymbokBundle\Tests\Command;

use Mtarld\SymbokBundle\Command\SavedUpdaterCommand;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Replacer\SavedClassReplacer;
use org\bovigo\vfs\vfsStream;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @group functional
 * @group command
 */
class SavedUpdaterCommandTest extends KernelTestCase
{
    /** @var CommandTester */
    private $commandTester;

    /** @var array<string> */
    private $savedFiles = [];

    /**
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PossiblyNullArgument
     */
    public function setUp(): void
    {
        $fixturesDir = __DIR__.'/../Fixtures/files';

        /** @var array<SplFileInfo> $files */
        $files = (new Finder())->name('*.php')->in($fixturesDir);
        foreach ($files as $file) {
            $filename = $file->getFilename();
            $content = file_get_contents($file->getRealPath());

            $this->savedFiles[$filename] = $content;
        }

        $virtualDirectory = ['src' => $this->savedFiles];

        vfsStream::setup('dir', 666, $virtualDirectory);

        static::bootKernel();
        $this->commandTester = new CommandTester(new SavedUpdaterCommand(
            static::$container->get(PhpCodeParser::class),
            static::$container->get(PhpCodeFinder::class),
            static::$container->get(SavedClassReplacer::class),
            ['Mtarld\SymbokBundle\Tests\Fixtures\files'],
            vfsStream::url('dir')
        ));
    }

    public function testSavedFilesAreUpdated(): void
    {
        $this->commandTester->execute([
            'directory' => 'src',
        ]);
        $virtualDirectoryPath = vfsStream::url('dir').'/src/';

        $this->assertSame(
            $this->savedFiles['ProductFromAnotherNamespace.php'],
            file_get_contents($virtualDirectoryPath.'ProductFromAnotherNamespace.php')
        );

        $this->assertSame(
            '<?php

namespace Mtarld\SymbokBundle\Tests\Fixtures\files;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;
use Mtarld\SymbokBundle\Annotation\Getter;
use Mtarld\SymbokBundle\Annotation\Setter;

/**
 * Description.
 *
 * @Symbok\ToString (properties={"id", "name"})
 * @Symbok\Data (fluent=true, nullable=true, constructorNullable=false)
 * @method self setId(?int $id)
 * @method string|null getName()
 * @method self setName(string $name)
 * @method mixed getImage()
 */
class Product1
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Getter()
     */
    private $id;

    /**
     * @var string
     * @Symbok\Nullable()
     * @Setter(fluent=true, nullable=false)
     */
    private $name;

    private $image;

    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
}
',
            file_get_contents($virtualDirectoryPath.'Product1.php')
        );

        $this->assertSame(
            '<?php

namespace Mtarld\SymbokBundle\Tests\Fixtures\files;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;

/**
 * @method mixed __construct()
 * @method \Doctrine\Common\Collections\Collection|null getPrices()
 * @method self removePrice(\App\Entity\Price $price)
 * @method \App\Entity\Category|null getCategory()
 * @method void setCategory(?\App\Entity\Category $category)
 */
class Product2
{
    /**
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Price", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     * @Symbok\Getter(nullable=true)
     * @Symbok\Setter(add=false)
     */
    private $prices;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @Symbok\Getter()
     * @Symbok\Setter(fluent=false)
     */
    private $category;

    public function addPrice($price)
    {
        $this->prices->add($price);
    }
}
',
            file_get_contents($virtualDirectoryPath.'Product2.php')
        );

        $this->assertSame(
            '<?php

namespace Mtarld\SymbokBundle\Tests\Fixtures\files;

use Doctrine\ORM\Mapping as ORM;
use Mtarld\SymbokBundle\Annotation as Symbok;
use Mtarld\SymbokBundle\Annotation\Getter;

/**
 * Description.
 *
 * @author Mathias Arlaud
 * @Symbok\ToString (properties={"id", "name"})
 * @Symbok\Data
 * @method int|null getNbCall()
 * @method mixed __construct(?int $id)
 * @method string __toString()
 * @method int|null getId()
 * @method self setId(?int $id)
 */
class Product3
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Getter()
     */
    private $id;
}
',
            file_get_contents($virtualDirectoryPath.'Product3.php')
        );
    }
}
