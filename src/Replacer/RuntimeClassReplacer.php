<?php

namespace Mtarld\SymbokBundle\Replacer;

use Mtarld\SymbokBundle\Compiler\CompilerInterface;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Parser\PhpCodeParser;
use Mtarld\SymbokBundle\Visitor\ReplaceClassNodeVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;

/**
 * @internal
 * @final
 */
class RuntimeClassReplacer implements ReplacerInterface
{
    /** @var CompilerInterface */
    private $compiler;

    /** @var ReplaceClassNodeVisitor */
    private $replaceClassNodeVisitor;

    /** @var PhpCodeParser */
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

    /**
     * @return array<Node>
     */
    private function getUpdatedStatements(string $className): array
    {
        $statements = $this->codeParser->parseStatements($className);
        $class = $this->compiler->compile($statements);

        return $this->replaceClass($statements, $class);
    }

    /**
     * @param array<Node> $statements
     *
     * @return array<Node>
     */
    private function replaceClass(array $statements, SymbokClass $class): array
    {
        $this->replaceClassNodeVisitor->class = $class;
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->replaceClassNodeVisitor);

        return $traverser->traverse($statements);
    }

    /**
     * @param array<Node> $statements
     */
    private function serializeStatements(array $statements): string
    {
        return (new Standard())->prettyPrintFile($statements);
    }
}
