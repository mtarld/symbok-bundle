<?php

namespace Mtarld\SymbokBundle\Compiler;

use Mtarld\SymbokBundle\Factory\ClassFactory;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Mixed_;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Psr\Log\LoggerInterface;

class SavedClassCompiler implements CompilerInterface
{
    private $classFactory;
    private $runtimeClassCompiler;
    private $codeFinder;
    private $logger;

    public function __construct(
        ClassFactory $classFactory,
        RuntimeClassCompiler $runtimeClassCompiler,
        PhpCodeFinder $codeFinder,
        LoggerInterface $symbokLogger
    ) {
        $this->classFactory = $classFactory;
        $this->runtimeClassCompiler = $runtimeClassCompiler;
        $this->codeFinder = $codeFinder;
        $this->logger = $symbokLogger;
    }

    public function compile(array $statements): SymbokClass
    {
        $initialClass = $this->classFactory->create($statements);
        $runtimeClass = $this->runtimeClassCompiler->compile($statements);

        $docBlock = $this->getUpdatedDocBlock($initialClass, $runtimeClass);
        $initialClass->setDocBlock($docBlock);

        return $initialClass;
    }

    private function getUpdatedDocBlock(SymbokClass $initial, SymbokClass $runtime): DocBlock
    {
        $updatedMethodTags = $this->getUpdatedMethodTags($initial, $runtime);

        $docBlock = $initial->getDocBlock();

        $tags = array_filter($docBlock->getTags(), function (Tag $tag) {
            return !$tag instanceof Method;
        });

        $tags = array_merge($tags, $updatedMethodTags);

        return new DocBlock(
            $docBlock->getSummary() ?? '',
            $docBlock->getDescription(),
            $tags,
            $docBlock->getContext()
        );
    }

    private function getUpdatedMethodTags(SymbokClass $initial, SymbokClass $runtime): array
    {
        $initialMethods = $this->codeFinder->findMethods($initial->getStatements());
        $runtimeMethods = $this->codeFinder->findMethods($runtime->getStatements());

        $addedMethods = array_udiff($runtimeMethods, $initialMethods, function (ClassMethod $inital, ClassMethod $added) {
            return strcmp($inital->name->name, $added->name->name);
        });

        $initalMethodTags = array_filter($initial->getDocBlock()->getTags(), function (Tag $tag) {
            return 'method' === $tag->getName();
        });
        $addedMethodTags = array_map(function (ClassMethod $method) {
            return $this->createMethodTag($method);
        }, $addedMethods);

        // Remove method tags that we'll replace
        $initalMethodTags = array_udiff($initalMethodTags, $addedMethodTags, function (Method $initial, Method $added) {
            return strcmp($initial->getMethodName(), $added->getMethodName());
        });

        return array_merge($initalMethodTags, $addedMethodTags);
    }

    private function createMethodTag(ClassMethod $method): Method
    {
        $arguments = array_map(function (Param $param) {
            $type = $param->type instanceof NullableType
                  ? '?'.$param->type->type
                  : (string) $param->type
            ;

            return [
                'name' => $param->var->name,
                'type' => $type,
            ];
        }, $method->getParams());

        $returnType = $method->getReturnType();

        $returnType = $returnType instanceof NullableType
                    ? '?'.$returnType->type
                    : (string) $returnType
        ;

        return new Method(
            $method->name->name,
            $arguments,
            !empty($returnType) ? (new TypeResolver())->resolve($returnType) : new Mixed_(),
            $method->isStatic()
        );
    }
}
