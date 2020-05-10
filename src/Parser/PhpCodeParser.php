<?php

namespace Mtarld\SymbokBundle\Parser;

use Exception;
use Mtarld\SymbokBundle\Autoload\AutoloadFinder;
use Mtarld\SymbokBundle\Exception\IOException;
use PhpParser\Node;
use PhpParser\ParserFactory;

/**
 * @internal
 * @final
 */
class PhpCodeParser
{
    /** @var AutoloadFinder */
    private $autoloadFinder;

    public function __construct(AutoloadFinder $autoloadFinder)
    {
        $this->autoloadFinder = $autoloadFinder;
    }

    /**
     * @return array<Node>
     */
    public function parseStatements(string $className): array
    {
        return $this->parseStatementsFromPath($this->autoloadFinder->findFile($className));
    }

    /**
     * @return array<Node>
     */
    public function parseStatementsFromPath(string $path): array
    {
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

        try {
            $content = file_get_contents($path);
        } catch (Exception $e) {
            throw new IOException(sprintf("Cannot read file '%s'. Exception: %s", $path, $e->getMessage()));
        }

        if (false === $content) {
            throw new IOException(sprintf("Cannot read file '%s'", $path));
        }

        return $parser->parse($content) ?? [];
    }
}
