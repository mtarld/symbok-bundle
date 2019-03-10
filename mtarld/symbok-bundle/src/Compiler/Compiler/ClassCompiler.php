<?php

namespace Mtarld\SymbokBundle\Compiler\Compiler;

use Doctrine\Common\Annotations\DocParser;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedClass;
use Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\ClassParser;
use Mtarld\SymbokBundle\Compiler\Generator\Generator;
use Mtarld\SymbokBundle\Compiler\Rules\Impl\ClassRules;
use Mtarld\SymbokBundle\Compiler\Statements;
use Mtarld\SymbokBundle\Tags\Updater as TagsUpdater;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class ClassCompiler
{
    /** @var DocParser */
    private $docParser;

    public function __construct()
    {
        $this->docParser = new DocParser();
        $this->docParser->setIgnoreNotImportedAnnotations(true);
        $this->docParser->setIgnoredAnnotationNames(['package', 'author']);
        $this->docParser->addNamespace('Mtarld\SymbokBundle\Annotation');
        $this->docParser->addNamespace('Doctrine\ORM\Mapping');
    }

    public function compile(NodeClass $class, Context $context): void
    {
        // Statements that will be merged (and replace base class statements)
        $statements = new Statements();

        $classParser = new ClassParser($this->docParser);
        $parsedClass = $classParser->parse($class, $context);

        $classRules = new ClassRules($parsedClass);

        $this->compileClassLevel($parsedClass, $context, $statements);
        $this->compilePropertyLevel($parsedClass, $context, $statements, $classRules);

        $class->setDocComment($this->getDocBlockWithoutMethodTag($parsedClass));

        // Append new statements
        foreach ($statements as $statement) {
            $class->stmts[] = $statement;
        }
    }

    private function compileClassLevel(ParsedClass $class, Context $context, Statements $statements): void
    {
        $generator = new Generator($context, new Serializer());
        $classRules = new ClassRules($class);
        if ($classRules->requiresAllArgsConstructor()) {
            $statements->merge(
                $generator->generateAllArgsConstructor($class, $classRules)
            );
        }
        // if ($classContext->requiresEqualTo()) {
        //     $statements->merge(
        //         $this->generator->generateEqualTo($className, ...$properties)
        //     );
        // }
    }

    private function compilePropertyLevel(
        ParsedClass $class,
        Context $context,
        Statements $statements,
        ClassRules $classRules
    ): void {
        $propertiesCompiler = new PropertiesCompiler();
        $propertiesCompiler->compile($class, $context, $statements, $classRules);
    }

    private function getDocBlockWithoutMethodTag(ParsedClass $class): Doc
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
        $docComment = TagsUpdater::removeSpaceFromClassTags($docComment);

        return new Doc(str_replace("/**\n * \n *\n", "/**\n", $docComment));
    }
}
