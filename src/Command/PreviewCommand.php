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
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class PreviewCommand extends Command implements ServiceSubscriberInterface
{
    public const COMPILATION_RUNTIME = 'runtime';
    public const COMPILATION_SAVED = 'saved';

    /** @var ContainerInterface */
    private $locator;

    /** @var PhpCodeParser */
    private $codeParser;

    /** @var PhpCodeFinder */
    private $codeFinder;

    /** @var array<array-key, string> */
    private $namespaces;

    protected static $defaultName = 'symbok:preview';

    public function __construct(
        ContainerInterface $locator,
        PhpCodeParser $codeParser,
        PhpCodeFinder $codeFinder,
        array $namespaces
    ) {
        $this->locator = $locator;
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

        $statements = $this->codeParser->parseStatementsFromPath($path);
        if (!in_array($this->codeFinder->findNamespaceName($statements), $this->namespaces, true)) {
            throw new InvalidArgumentException(sprintf('File %s is not in specified namespaces: %s', $path, implode(', ', $this->namespaces)));
        }

        $classFqcn = $this->codeFinder->findFqcn($statements);

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf("'%s' '%s' compilation preview", $classFqcn, $strategy));

        $io->write($this->getReplacer($strategy)->replace($classFqcn));

        return 0;
    }

    private function getReplacer(string $strategy): ReplacerInterface
    {
        switch ($strategy) {
            case self::COMPILATION_RUNTIME:
                return $this->locator->get('symbok.replacer.runtime');
            case self::COMPILATION_SAVED:
                return $this->locator->get('symbok.replacer.saved');
            default:
                throw new InvalidArgumentException(sprintf("compilationStrategy must be either '%s' or '%s'", self::COMPILATION_RUNTIME, self::COMPILATION_SAVED));
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            'symbok.replacer.runtime' => ReplacerInterface::class,
            'symbok.replacer.saved' => ReplacerInterface::class,
        ];
    }
}
