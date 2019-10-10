<?php

namespace Mtarld\SymbokBundle\Command;

use Mtarld\SymbokBundle\Exception\RuntimeException;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PreviewCommand extends Command
{
    public const COMPILATION_RUNTIME = 'runtime';
    public const COMPILATION_SAVED = 'saved';

    private $container;
    private $codeParser;
    private $codeFinder;
    private $namespaces;

    protected static $defaultName = 'symbok:preview';

    public function __construct(
        ContainerInterface $container,
        PhpCodeParser $codeParser,
        PhpCodeFinder $codeFinder,
        array $config
    ) {
        $this->container = $container;
        $this->codeParser = $codeParser;
        $this->codeFinder = $codeFinder;
        $this->namespaces = $config['namespaces'];

        parent::__construct();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function configure()
    {
        $this->addOption(
            'compilationStrategy',
            's',
            InputOption::VALUE_REQUIRED,
            sprintf("Which compilation strategy to use ? ('%s' or '%s')", self::COMPILATION_RUNTIME, self::COMPILATION_SAVED),
            self::COMPILATION_RUNTIME
        );

        $this->addArgument('path', InputArgument::REQUIRED, 'Class file to be previewed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $replacer = $this->getReplacer($input->getOption('compilationStrategy'));
        $className = $this->getClassName($input->getArgument('path'));

        echo $replacer->replace($className);

        return 0;
    }

    private function getReplacer(string $option): ReplacerInterface
    {
        switch ($option) {
            case self::COMPILATION_RUNTIME:
                return $this->container->get('symbok.replacer.runtime_class');
                break;
            case self::COMPILATION_SAVED:
                return $this->container->get('symbok.replacer.saved_class');
                break;
            default:
                throw new RuntimeException(sprintf("compilationStrategy must be either '%s' or '%s'", self::COMPILATION_RUNTIME, self::COMPILATION_SAVED));
                break;
        }
    }

    private function getClassName(string $path): string
    {
        $statements = $this->codeParser->parseStatementsFromPath($path);
        $namespace = (string) $this->codeFinder->findNamespace($statements)->name;

        if (!in_array($namespace, $this->namespaces)) {
            throw new RuntimeException(sprintf('Class is not in specified namespaces: %s', implode(', ', $this->namespaces)));
        }

        return $namespace.'\\'.$this->codeFinder->findClass($statements)->name;
    }
}
