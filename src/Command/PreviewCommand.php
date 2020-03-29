<?php

namespace Mtarld\SymbokBundle\Command;

use InvalidArgumentException;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PreviewCommand extends Command
{
    public const COMPILATION_RUNTIME = 'runtime';
    public const COMPILATION_SAVED = 'saved';

    /** @var ContainerInterface */
    private $container;

    /** @var PhpCodeParser */
    private $codeParser;

    /** @var PhpCodeFinder */
    private $codeFinder;

    /** @var array<array-key, string> */
    private $namespaces;

    protected static $defaultName = 'symbok:preview';

    public function __construct(
        ContainerInterface $container,
        PhpCodeParser $codeParser,
        PhpCodeFinder $codeFinder,
        array $namespaces
    ) {
        $this->container = $container;
        $this->codeParser = $codeParser;
        $this->codeFinder = $codeFinder;
        $this->namespaces = $namespaces;

        parent::__construct();
    }

    protected function configure(): void
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
        /** @var string $strategy */
        $strategy = $input->getOption('compilationStrategy');

        /** @var string $path */
        $path = $input->getArgument('path');

        $replacer = $this->getReplacer($strategy);
        $className = $this->getClassName($path);

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf("'%s' '%s' compilation preview", $className, $strategy));
        $io->write($replacer->replace($className));

        return 0;
    }

    private function getReplacer(string $strategy): ReplacerInterface
    {
        switch ($strategy) {
            case self::COMPILATION_RUNTIME:
                return $this->container->get('symbok.replacer.runtime_class');
            case self::COMPILATION_SAVED:
                return $this->container->get('symbok.replacer.saved_class');
            default:
                throw new InvalidArgumentException(sprintf("compilationStrategy must be either '%s' or '%s'", self::COMPILATION_RUNTIME, self::COMPILATION_SAVED));
        }
    }

    private function getClassName(string $path): string
    {
        $statements = $this->codeParser->parseStatementsFromPath($path);
        $namespace = $this->codeFinder->findNamespaceName($statements);

        if (!in_array($namespace, $this->namespaces, true)) {
            throw new InvalidArgumentException(sprintf('Class is not in specified namespaces: %s', implode(', ', $this->namespaces)));
        }

        return $namespace.'\\'.$this->codeFinder->findClassName($statements);
    }
}
