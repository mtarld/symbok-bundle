<?php

namespace Mtarld\SymbokBundle\Tests\Command;

use Mtarld\SymbokBundle\Command\PreviewCommand;
use Mtarld\SymbokBundle\Exception\RuntimeException;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @group functional
 * @group command
 */
class PreviewCommandTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel();
    }

    public function testWrongCompilationStrategy(): void
    {
        $command = new PreviewCommand(
            self::$container,
            self::$container->get(PhpCodeParser::class),
            self::$container->get(PhpCodeFinder::class),
            ['namespaces' => ['Mtarld\SymbokBundle\Tests\Fixtures\files']]
        );

        $input = $this->createMock(InputInterface::class);
        $input
            ->method('getOption')
            ->willReturn('foo')
        ;

        $this->expectException(RuntimeException::class);

        $command->run(
            $input,
            $this->createMock(OutputInterface::class)
        );
    }

    public function testWrongPath(): void
    {
        $command = new PreviewCommand(
            self::$container,
            self::$container->get(PhpCodeParser::class),
            self::$container->get(PhpCodeFinder::class),
            ['namespaces' => ['Mtarld\SymbokBundle\Tests\Fixtures\files']]
        );

        $input = $this->createMock(InputInterface::class);
        $input
            ->method('getOption')
            ->willReturn(PreviewCommand::COMPILATION_RUNTIME)
        ;
        $input
            ->method('getArgument')
            ->willReturn('foo')
        ;

        $this->expectException(RuntimeException::class);

        $command->run(
            $input,
            $this->createMock(OutputInterface::class)
        );
    }

    public function testWrongNamespace(): void
    {
        $command = new PreviewCommand(
            self::$container,
            self::$container->get(PhpCodeParser::class),
            self::$container->get(PhpCodeFinder::class),
            ['namespaces' => ['foo']]
        );

        $input = $this->createMock(InputInterface::class);
        $input
            ->method('getOption')
            ->willReturn(PreviewCommand::COMPILATION_RUNTIME)
        ;
        $input
            ->method('getArgument')
            ->willReturn(
                __DIR__
                .DIRECTORY_SEPARATOR.'..'
                .DIRECTORY_SEPARATOR.'Fixtures'
                .DIRECTORY_SEPARATOR.'files'
                .DIRECTORY_SEPARATOR.'Product1.php'
            )
        ;

        $this->expectException(RuntimeException::class);

        $command->run(
            $input,
            $this->createMock(OutputInterface::class)
        );
    }

    public function testPreviewRuntime(): void
    {
        $command = new PreviewCommand(
            self::$container,
            self::$container->get(PhpCodeParser::class),
            self::$container->get(PhpCodeFinder::class),
            ['namespaces' => ['Mtarld\SymbokBundle\Tests\Fixtures\files']]
        );

        $input = $this->createMock(InputInterface::class);
        $input
            ->method('getOption')
            ->willReturn(PreviewCommand::COMPILATION_RUNTIME)
        ;
        $input
            ->method('getArgument')
            ->willReturn(
                __DIR__
                .DIRECTORY_SEPARATOR.'..'
                .DIRECTORY_SEPARATOR.'Fixtures'
                .DIRECTORY_SEPARATOR.'files'
                .DIRECTORY_SEPARATOR.'Product1.php'
            )
        ;

        $this->expectOutputString(
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
}'
        );

        $command->run(
            $input,
            $this->createMock(OutputInterface::class)
        );
    }

    public function testPreviewSaved(): void
    {
        $command = new PreviewCommand(
            self::$container,
            self::$container->get(PhpCodeParser::class),
            self::$container->get(PhpCodeFinder::class),
            ['namespaces' => ['Mtarld\SymbokBundle\Tests\Fixtures\files']]
        );

        $input = $this->createMock(InputInterface::class);
        $input
            ->method('getOption')
            ->willReturn(PreviewCommand::COMPILATION_SAVED)
        ;
        $input
            ->method('getArgument')
            ->willReturn(
                __DIR__
                .DIRECTORY_SEPARATOR.'..'
                .DIRECTORY_SEPARATOR.'Fixtures'
                .DIRECTORY_SEPARATOR.'files'
                .DIRECTORY_SEPARATOR.'Product1.php'
            )
        ;

        $this->expectOutputString(
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
'
        );

        $command->run(
            $input,
            $this->createMock(OutputInterface::class)
        );
    }
}
