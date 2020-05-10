<?php

namespace Mtarld\SymbokBundle\Replacer;

use Mtarld\SymbokBundle\Autoload\AutoloadFinder;
use Mtarld\SymbokBundle\Compiler\SavedClassCompiler;
use Mtarld\SymbokBundle\Exception\IOException;
use Mtarld\SymbokBundle\Factory\DocFactory;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use RuntimeException;

class SavedClassReplacer implements ReplacerInterface
{
    /** @var SavedClassCompiler */
    private $compiler;

    /** @var DocFactory */
    private $docFactory;

    /** @var PhpCodeFinder */
    private $codeFinder;

    /** @var PhpCodeParser */
    private $codeParser;

    /** @var AutoloadFinder */
    private $autoloaderFinder;

    public function __construct(
        SavedClassCompiler $compiler,
        DocFactory $docFactory,
        PhpCodeFinder $codeFinder,
        PhpCodeParser $codeParser,
        AutoloadFinder $autoloaderFinder
    ) {
        $this->compiler = $compiler;
        $this->docFactory = $docFactory;
        $this->codeFinder = $codeFinder;
        $this->codeParser = $codeParser;
        $this->autoloaderFinder = $autoloaderFinder;
    }

    public function replace(string $className): string
    {
        $path = $this->autoloaderFinder->findFile($className);

        if (false === $fp = fopen($path, 'rwb+')) {
            throw new IOException(sprintf("Cannot open file '%s'", $path));
        }

        $content = $this->updateContent($path, $fp);
        fclose($fp);

        return $content;
    }

    /**
     * @param resource $fp
     */
    private function updateContent(string $path, $fp): string
    {
        flock($fp, LOCK_EX);

        $statements = $this->codeParser->parseStatementsFromPath($path);
        $originalClass = $this->codeFinder->findClass($statements);

        $originalDoc = $originalClass->getDocComment();
        $updatedDoc = $this->getUpdatedDoc($statements);

        if ($originalDoc instanceof Doc) {
            $filePos = method_exists($originalDoc, 'getStartFilePos') ? $originalDoc->getStartFilePos() : $originalDoc->getFilePos();
        }
        $filePos = $filePos ?? $this->getOriginalClassFilePos($originalClass, $fp);

        $content = $this->getUpdatedContent($originalDoc, $updatedDoc, $fp, $filePos);

        flock($fp, LOCK_UN);

        return $content;
    }

    /**
     * @param resource $fp
     */
    private function getUpdatedContent(?Doc $original, Doc $updated, $fp, int $filePos): string
    {
        fseek($fp, 0);

        $lineBreak = $original instanceof Doc ? '' : PHP_EOL;
        $content = fread($fp, $filePos).$updated.$lineBreak;

        if ($original instanceof Doc) {
            fseek($fp, strlen((string) $original), SEEK_CUR);
        }

        if (false === $stat = fstat($fp)) {
            throw new IOException('Cannot stat class file');
        }
        $content .= fread($fp, $stat['size'] - ftell($fp));

        return $content;
    }

    /**
     * @param resource $fp
     */
    private function getOriginalClassFilePos(Class_ $class, $fp): int
    {
        $currentLine = 1;
        while (false === feof($fp) && $currentLine < $class->getLine()) {
            fgets($fp);
            if ($class->getLine() - 1 === $currentLine) {
                if (false === $position = ftell($fp)) {
                    throw new IOException('Cannot find position of file pointer');
                }

                return $position;
            }

            ++$currentLine;
        }

        throw new RuntimeException('Class position was not found');
    }

    /**
     * @param array<Node> $statements
     */
    private function getUpdatedDoc(array $statements): Doc
    {
        $docBlock = $this->compiler
            ->compile($statements)
            ->getDocBlock()
        ;

        return $this->docFactory->createFromDocBlock($docBlock);
    }
}
