<?php

namespace Mtarld\SymbokBundle\Tests\Command;

use Mtarld\SymbokBundle\Command\SavedUpdaterCommand;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Replacer\SavedClassReplacer;
use org\bovigo\vfs\vfsStream;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * @group functional
 * @group command
 */
class SavedUpdaterCommandTest extends KernelTestCase
{
    private $savedFiles = [];

    public function setUp(): void
    {
        $fixturesDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'files';

        $files = (new Finder())->name('*.php')->in($fixturesDir);
        foreach ($files as $file) {
            $filename = $file->getFilename();
            $content = file_get_contents($file->getRealPath());

            $this->savedFiles[$filename] = $content;
        }

        $virtualDirectory = ['src' => $this->savedFiles];

        vfsStream::setup('dir', 666, $virtualDirectory);

        static::bootKernel();
    }

    public function testSavedFilesAreUpdated(): void
    {
        $command = new SavedUpdaterCommand(
            self::$container->get(PhpCodeParser::class),
            self::$container->get(PhpCodeFinder::class),
            self::$container->get(SavedClassReplacer::class),
            ['namespaces' => ['Mtarld\SymbokBundle\Tests\Fixtures\files']],
            vfsStream::url('dir')
        );

        $output = $this->createMock(OutputInterface::class);
        $output
            ->expects($this->exactly(sizeof($this->savedFiles)))
            ->method('writeln')
        ;

        $input = $this->createMock(InputInterface::class);
        $input
            ->method('getArgument')
            ->willReturn('src')
        ;

        $command->run(
            $input,
            $output
        );

        $virtualDirectoryPath = vfsStream::url('dir').DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR;

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
 * @method ?string getName()
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
 * @method ?\Doctrine\Common\Collections\Collection getPrices()
 * @method self removePrice(App\Entity\Price $price)
 * @method ?\App\Entity\Category getCategory()
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
 * @method int getNbCall()
 * @method mixed __construct(?int $id)
 * @method string __toString()
 * @method ?int getId()
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
