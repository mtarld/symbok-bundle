<?php

namespace Mtarld\SymbokBundle\Command;

use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Replacer\ReplacerInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class SavedUpdaterCommand extends Command
{
    /** @var PhpCodeParser */
    private $codeParser;

    /** @var PhpCodeFinder */
    private $codeFinder;

    /** @var ReplacerInterface */
    private $replacer;

    /** @var array<array-key, string> */
    private $namespaces;

    /** @var string */
    private $projectDir;

    protected static $defaultName = 'symbok:update:classes';

    public function __construct(
        PhpCodeParser $codeParser,
        PhpCodeFinder $codeFinder,
        ReplacerInterface $replacer,
        array $namespaces,
        string $projectDir
    ) {
        $this->codeFinder = $codeFinder;
        $this->codeParser = $codeParser;
        $this->replacer = $replacer;
        $this->namespaces = $namespaces;
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('directory', InputArgument::OPTIONAL, 'Directory where classes are located', 'src');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $directory */
        $directory = $input->getArgument('directory');
        $directory = $this->projectDir.'/'.$directory;

        /** @var array<SplFileInfo> $classFiles */
        $classFiles = (new Finder())->name('*.php')->in($directory);

        $io = new SymfonyStyle($input, $output);
        $io->title('Update original classes');
        if (!$io->isVerbose()) {
            $io->progressStart(count($classFiles));
        }

        foreach ($classFiles as $classFile) {
            try {
                $this->updateFile($classFile);
                $io->writeln(sprintf('\'%s\' processed.', $classFile->getRelativePathname()), OutputInterface::VERBOSITY_VERBOSE);
            } catch (RuntimeException $e) {
                $io->writeln(sprintf('Skipping \'%s\': %s', $classFile->getRelativePathname(), $e->getMessage()), OutputInterface::VERBOSITY_VERBOSE);
            } finally {
                if (!$io->isVerbose()) {
                    $io->progressAdvance();
                }
            }
        }

        if (!$io->isVerbose()) {
            $io->progressFinish();
        }

        return 0;
    }

    private function updateFile(SplFileInfo $file): void
    {
        $statements = $this->codeParser->parseStatementsFromPath($file->getPathname());
        if (!in_array($this->codeFinder->findNamespaceName($statements), $this->namespaces, true)) {
            return;
        }

        file_put_contents($file->getPathname(), $this->replacer->replace($this->codeFinder->findFqcn($statements)));
    }
}
