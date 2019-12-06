<?php

namespace Mtarld\SymbokBundle\Command;

use Mtarld\SymbokBundle\Exception\RuntimeException;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class SavedUpdaterCommand extends Command
{
    private $codeParser;
    private $codeFinder;
    private $replacer;
    private $namespaces;
    private $projectDir;

    protected static $defaultName = 'symbok:update:classes';

    public function __construct(
        PhpCodeParser $codeParser,
        PhpCodeFinder $codeFinder,
        ReplacerInterface $replacer,
        array $config,
        string $projectDir
    ) {
        $this->codeFinder = $codeFinder;
        $this->codeParser = $codeParser;
        $this->replacer = $replacer;
        $this->namespaces = $config['namespaces'];
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function configure()
    {
        $this->addArgument('directory', InputArgument::OPTIONAL, 'Directory where classes are located', 'src');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = $this->projectDir.DIRECTORY_SEPARATOR.$input->getArgument('directory');
        $classFiles = (new Finder())
                    ->name('*.php')
                    ->in($directory)
        ;

        foreach ($classFiles as $classFile) {
            try {
                $this->updateFile($classFile);
                $output->writeln(sprintf('\'%s\' processed.', $classFile->getRelativePathname()), OutputInterface::VERBOSITY_VERBOSE);
            } catch (RuntimeException $e) {
                $output->writeln(sprintf('Skipping \'%s\': %s', $classFile->getRelativePathname(), $e->getMessage()), OutputInterface::VERBOSITY_VERBOSE);
            }
        }

        return 0;
    }

    private function updateFile(SplFileInfo $file): void
    {
        $path = $file->getPathName();
        $statements = $this->codeParser->parseStatementsFromPath($path);

        $namespace = (string) $this->codeFinder->findNamespace($statements)->name;
        if (!in_array($namespace, $this->namespaces)) {
            throw new RuntimeException(sprintf('Not in specified namespaces: %s', implode(', ', $this->namespaces)));
        }

        $class = $namespace.'\\'.$this->codeFinder->findClass($statements)->name;

        file_put_contents($path, $this->replacer->replace($class));
    }
}
