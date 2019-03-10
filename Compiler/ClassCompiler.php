<?php

namespace Mtarld\SymbokBundle\Compiler;

use Mtarld\SymbokBundle\Factory\Symbok\SymbokClassFactory;
use Mtarld\SymbokBundle\Model\Statements;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use Mtarld\SymbokBundle\Service\MethodGeneratorService;
use Mtarld\SymbokBundle\Service\TagsUpdaterService;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Serializer;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class ClassCompiler
{
    /** @var SymbokClassFactory */
    private $classFactory;

    /** @var PropertyCompiler */
    private $propertyCompiler;

    /** @var MethodGeneratorService */
    private $methodGeneratorService;

    public function __construct(
        SymbokClassFactory $classFactory,
        PropertyCompiler $propertyCompiler,
        MethodGeneratorService $methodGeneratorService
    ) {
        $this->classFactory = $classFactory;
        $this->propertyCompiler = $propertyCompiler;
        $this->methodGeneratorService = $methodGeneratorService;
    }

    public function compile(NodeClass $class): void
    {
        // Statements that will be merged (and replace base class statements)
        $statements = new Statements();

        $symbokClass = $this->classFactory->create($class);

        $this->compileClassLevel($symbokClass, $statements);
        $this->compilePropertyLevel($symbokClass, $statements);

        $class->setDocComment($this->getDocBlockWithoutMethodTag($symbokClass));

        // Append new statements
        foreach ($statements as $statement) {
            $class->stmts[] = $statement;
        }
    }

    private function compileClassLevel(SymbokClass $class, Statements $statements): void
    {
        if ($class->getRules()->requiresAllArgsConstructor()) {
            $statements->merge(
                $this->methodGeneratorService->generateAllArgsConstructor($class)
            );
        }
        if ($class->getRules()->requiresToString()) {
            $statements->merge(
                $this->methodGeneratorService->generateToString($class)
            );
        }
    }

    private function compilePropertyLevel(SymbokClass $class, Statements $statements): void
    {
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $this->propertyCompiler->compile($class, $property, $statements);
        }
    }

    private function getDocBlockWithoutMethodTag(SymbokClass $class): Doc
    {
        $docBlock = $class->getDocBlock();
        $tags = $docBlock->getTags();
        foreach ($tags as $index => $tag) {
            if ($tag instanceof DocBlock\Tags\Method) {
                unset($tags[$index]);
            }
        }

        $serializer = new Serializer();
        $docComment = $serializer->getDocComment(new DocBlock(
            $docBlock->getSummary(),
            $docBlock->getDescription(),
            $tags
        ));
        $docComment = TagsUpdaterService::removeSpaceFromClassTags($docComment);

        return new Doc(str_replace("/**\n * \n *\n", "/**\n", $docComment));
    }
}
