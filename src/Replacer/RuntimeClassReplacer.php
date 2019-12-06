<?php

namespace Mtarld\SymbokBundle\Replacer;

use Mtarld\SymbokBundle\Compiler\CompilerInterface;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Visitor\ReplaceClassNodeVisitor;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;

class RuntimeClassReplacer implements ReplacerInterface
{
    private $compiler;
    private $replaceClassNodeVisitor;
    private $codeParser;

    public function __construct(
        CompilerInterface $compiler,
        ReplaceClassNodeVisitor $replaceClassNodeVisitor,
        PhpCodeParser $codeParser
    ) {
        $this->compiler = $compiler;
        $this->replaceClassNodeVisitor = $replaceClassNodeVisitor;
        $this->codeParser = $codeParser;
    }

    public function replace(string $class): string
    {
        return $this->serializeStatements(
            $this->getUpdatedStatements($class)
        );
    }

    private function getUpdatedStatements(string $className): array
    {
        $statements = $this->codeParser->parseStatements($className);
        $class = $this->compiler->compile($statements);

        return $this->replaceClass($statements, $class);
    }

    private function replaceClass(array $statements, SymbokClass $class): array
    {
        $this->replaceClassNodeVisitor->class = $class;
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->replaceClassNodeVisitor);

        return $traverser->traverse($statements);
    }

    private function serializeStatements(array $statements): string
    {
        return (new Standard())->prettyPrintFile($statements);
    }
}
