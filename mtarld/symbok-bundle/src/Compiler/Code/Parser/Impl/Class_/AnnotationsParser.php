<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Class_;

use Doctrine\Common\Annotations\DocParser;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Class_\ClassAnnotation;
use Mtarld\SymbokBundle\Compiler\Code\Parser\ParserInterface;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class AnnotationsParser implements ParserInterface
{
    /** @var DocParser */
    private $parser;

    public function __construct(DocParser $parser)
    {
        $this->parser = $parser;
    }

    public function parse($class, Context $context): array
    {
        /** @var NodeClass $class */
        $parsedAnnotations = [];
        if ($class->getDocComment()) {
            $commentText = $class->getDocComment()->getText();
            $commentText = $this->replaceAnnotationsNamespace($commentText, $context);

            foreach ($this->parser->parse($commentText) as $annotation) {
                $parsedAnnotations[] = new ClassAnnotation($annotation);
            }
        }

        return $parsedAnnotations;
    }

    private function replaceAnnotationsNamespace(string $comment, Context $context): string
    {
        $symbokAlias = null;
        foreach ($context->getNamespaceAliases() as $alias => $target) {
            if ($target == 'Mtarld\\SymbokBundle\\Annotation') {
                $symbokAlias = is_int($alias) ? 'Annotation' : $alias;
            }
        }

        $comment = str_replace('Mtarld\\Symbok\\Annotation\\', '', $comment);
        if ($symbokAlias) {
            $comment = str_replace("$symbokAlias\\", '', $comment);
        }

        return $comment;
    }
}
