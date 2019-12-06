<?php

namespace Mtarld\SymbokBundle\Replacer;

use Mtarld\SymbokBundle\Autoload\Autoload;
use Mtarld\SymbokBundle\Compiler\SavedClassCompiler;
use Mtarld\SymbokBundle\Exception\RuntimeException;
use Mtarld\SymbokBundle\Factory\DocFactory;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;

class SavedClassReplacer implements ReplacerInterface
{
    private $compiler;
    private $docFactory;
    private $codeFinder;
    private $codeParser;

    public function __construct(
        SavedClassCompiler $compiler,
        DocFactory $docFactory,
        PhpCodeFinder $codeFinder,
        PhpCodeParser $codeParser
    ) {
        $this->compiler = $compiler;
        $this->docFactory = $docFactory;
        $this->codeFinder = $codeFinder;
        $this->codeParser = $codeParser;
    }

    public function replace(string $className): string
    {
        $path = Autoload::getClassLoader()->findFile($className);
        $fp = fopen($path, 'rw+');

        $content = $this->updateContent($path, $fp);
        fclose($fp);

        return $content;
    }

    private function updateContent(string $path, $fp): string
    {
        flock($fp, LOCK_EX);

        $statements = $this->codeParser->parseStatementsFromPath($path);
        $originalClass = $this->codeFinder->findClass($statements);

        $originalDoc = $originalClass->getDocComment();
        $updatedDoc = $this->getUpdatedDoc($statements);

        $filePos = $originalDoc instanceof Doc ? $originalDoc->getFilePos() : $this->getOriginalClassFilePos($originalClass, $fp);

        $content = $this->getUpdatedContent($originalDoc, $updatedDoc, $fp, $filePos);

        flock($fp, LOCK_UN);

        return $content;
    }

    private function getUpdatedContent(?Doc $original, Doc $updated, $fp, int $filePos): string
    {
        fseek($fp, 0);

        $lineBreak = $original instanceof Doc ? '' : PHP_EOL;
        $content = fread($fp, $filePos).$updated.$lineBreak;

        if ($original instanceof Doc) {
            fseek($fp, strlen($original), SEEK_CUR);
        }

        $content .= fread($fp, fstat($fp)['size'] - ftell($fp));

        return $content;
    }

    private function getOriginalClassFilePos(Class_ $class, $fp): int
    {
        $currentLine = 1;
        while (false === feof($fp) && $currentLine < $class->getLine()) {
            fgets($fp);
            if ($class->getLine() - 1 === $currentLine) {
                return ftell($fp);
            }

            ++$currentLine;
        }

        throw new RuntimeException('Class position was not found');
    }

    private function getUpdatedDoc(array $statements): Doc
    {
        $docBlock = $this->compiler
            ->compile($statements)
            ->getDocBlock()
        ;

        return $this->docFactory->createFromDocBlock($docBlock);
    }
}
