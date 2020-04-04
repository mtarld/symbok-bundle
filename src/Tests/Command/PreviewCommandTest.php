<?php

namespace Mtarld\SymbokBundle\Tests\Command;

use InvalidArgumentException;
use Mtarld\SymbokBundle\Command\PreviewCommand;
use Mtarld\SymbokBundle\Exception\IOException;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 * @group command
 */
class PreviewCommandTest extends KernelTestCase
{
    /** @var CommandTester */
    private $commandTester;

    /**
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PossiblyNullArgument
     */
    public function setUp(): void
    {
        static::bootKernel();
        $this->commandTester = new CommandTester(new PreviewCommand(
            static::$container,
            static::$container->get(PhpCodeParser::class),
            static::$container->get(PhpCodeFinder::class),
            ['Mtarld\SymbokBundle\Tests\Fixtures\files']
        ));
    }

    public function testWrongCompilationStrategy(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->commandTester->execute([
            'path' => __DIR__.'/../Fixtures/files/Product1.php',
            '--compilationStrategy' => 'foo',
        ]);
    }

    public function testWrongPath(): void
    {
        $this->expectException(IOException::class);

        $this->commandTester->execute([
            'path' => 'foo',
        ]);
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PossiblyNullArgument
     */
    public function testWrongNamespace(): void
    {
        $command = new PreviewCommand(
            static::$container,
            static::$container->get(PhpCodeParser::class),
            static::$container->get(PhpCodeFinder::class),
            ['foo']
        );

        $this->expectException(InvalidArgumentException::class);
        (new CommandTester($command))->execute([
            'path' => __DIR__.'/../Fixtures/files/Product1.php',
        ]);
    }

    public function testPreviewRuntime(): void
    {
        $this->commandTester->execute([
            'path' => __DIR__.'/../Fixtures/files/Product1.php',
            '--compilationStrategy' => PreviewCommand::COMPILATION_RUNTIME,
        ]);

        $this->assertSame(
            '
\'Mtarld\SymbokBundle\Tests\Fixtures\files\Product1\' \'runtime\' compilation preview
=================================================================================

<?php

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
    public function setId(?int $id) : self
    {
        $this->id = $id;
        return $this;
    }
    public function getName() : ?string
    {
        return $this->name;
    }
    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }
    public function getImage()
    {
        return $this->image;
    }
}',
            $this->commandTester->getDisplay()
        );
    }

    public function testPreviewSaved(): void
    {
        $this->commandTester->execute([
            'path' => __DIR__.'/../Fixtures/files/Product1.php',
            '--compilationStrategy' => PreviewCommand::COMPILATION_SAVED,
        ]);

        $this->assertSame(
            '
\'Mtarld\SymbokBundle\Tests\Fixtures\files\Product1\' \'saved\' compilation preview
===============================================================================

<?php

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
            $this->commandTester->getDisplay()
        );
    }
}
