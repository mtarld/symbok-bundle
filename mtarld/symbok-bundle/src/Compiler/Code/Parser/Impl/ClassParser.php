<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parser\Impl;

use Doctrine\Common\Annotations\DocParser;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedClass;
use Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Class_\AnnotationsParser;
use Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Class_\MethodsParser;
use Mtarld\SymbokBundle\Compiler\Code\Parser\ParserInterface;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class ClassParser implements ParserInterface
{
    /** @var DocParser */
    private $parser;

    public function __construct(DocParser $parser)
    {
        $this->parser = $parser;
    }

    public function parse($class, Context $context): ParsedClass
    {
        /** @var NodeClass $class */
        $classMethodsParser = new MethodsParser($class);
        $classAnnotationsParser = new AnnotationsParser($this->parser);

        return new ParsedClass(
            $class->name->name,
            $classAnnotationsParser->parse($class, $context),
            $this->getDocBlock($class, $context),
            $this->getProperties($class, $context),
            $classMethodsParser->parse()
        );
    }

    private function getDocBlock(NodeClass $class, Context $context): DocBlock
    {
        if (empty((string)$class->getDocComment())) {
            return new DocBlock('', null, [], $context);
        }

        $comment = $class->getDocComment() !== null ? (string)$class->getDocComment()->getReformattedText() : null;

        return DocBlockFactory::createInstance()->create($comment, $context);
    }

    private function getProperties(NodeClass $class, $context): array
    {
        $propertyParser = new PropertiesParser($this->parser);

        return $propertyParser->parse($class, $context);
    }
}
