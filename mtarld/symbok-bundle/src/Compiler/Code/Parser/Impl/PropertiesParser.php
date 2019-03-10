<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parser\Impl;

use Doctrine\Common\Annotations\DocParser;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedProperty;
use Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Property\AnnotationsParser;
use Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Property\MethodsParser;
use Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Property\TypesParser;
use Mtarld\SymbokBundle\Compiler\Code\Parser\ParserInterface;
use Mtarld\SymbokBundle\Compiler\Helper\NodeFinder;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class PropertiesParser implements ParserInterface
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
        $classProperties = NodeFinder::findProperties(...$class->stmts);
        $className = $class->name->name;
        $properties = [];

        $propertyAnnotationsParser = new AnnotationsParser($this->parser);
        $propertyMethodsParser = new MethodsParser($class);
        $propertyTypesParser = new TypesParser();

        foreach ($classProperties as $property) {
            foreach ($property->props as $prop) {
                $annotations = $propertyAnnotationsParser->parse($property, $context);
                $types = $propertyTypesParser->parse($property, $annotations, $context);
                $methods = $propertyMethodsParser->parse($prop, $types);

                $properties[] = new ParsedProperty(
                    $prop->name->name,
                    $className,
                    $types,
                    $methods,
                    $annotations
                );
            }
        }

        return $properties;
    }
}
