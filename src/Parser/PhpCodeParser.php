<?php

namespace Mtarld\SymbokBundle\Parser;

use Exception;
use Mtarld\SymbokBundle\Autoload\Autoload;
use Mtarld\SymbokBundle\Exception\RuntimeException;
use PhpParser\ParserFactory;

class PhpCodeParser
{
    public function parseStatements(string $className): array
    {
        $path = Autoload::getClassLoader()->findFile($className);

        return $this->parseStatementsFromPath($path);
    }

    public function parseStatementsFromPath(string $path): array
    {
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

        try {
            $content = file_get_contents($path);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Cannot read file %s. Exception: %s', $path, $e->getMessage()));
        }

        return $parser->parse($content);
    }
}
