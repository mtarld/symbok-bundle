<?php

namespace Mtarld\SymbokBundle\Compiler;

use Mtarld\SymbokBundle\Exception\CodeFindingException;
use Mtarld\SymbokBundle\Factory\ClassFactory;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\Type;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * @internal
 * @final
 */
class SavedClassCompiler implements CompilerInterface
{
    /** @var ClassFactory */
    private $classFactory;

    /** @var RuntimeClassCompiler */
    private $runtimeClassCompiler;

    /** @var PhpCodeFinder */
    private $codeFinder;

    /** @var TypeFormatter */
    private $typeFormatter;

    public function __construct(
        ClassFactory $classFactory,
        RuntimeClassCompiler $runtimeClassCompiler,
        PhpCodeFinder $codeFinder,
        TypeFormatter $typeFormatter
    ) {
        $this->classFactory = $classFactory;
        $this->runtimeClassCompiler = $runtimeClassCompiler;
        $this->codeFinder = $codeFinder;
        $this->typeFormatter = $typeFormatter;
    }

    /**
     * @param array<Node> $statements
     */
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

        $tags = array_filter($docBlock->getTags(), static function (Tag $tag): bool {
            return !$tag instanceof Method;
        });

        $tags = array_merge($tags, $updatedMethodTags);

        return new DocBlock(
            $docBlock->getSummary() ?: '',
            $docBlock->getDescription(),
            $tags,
            $docBlock->getContext()
        );
    }

    /**
     * @return array<Tag>
     */
    private function getUpdatedMethodTags(SymbokClass $initial, SymbokClass $runtime): array
    {
        $initialMethods = $this->codeFinder->findMethods($initial->getStatements());
        $runtimeMethods = $this->codeFinder->findMethods($runtime->getStatements());
        $addedMethods = array_udiff($runtimeMethods, $initialMethods, static function (ClassMethod $initial, ClassMethod $added): int {
            return strcmp($initial->name->name, $added->name->name);
        });

        $initialMethodTags = array_filter($initial->getDocBlock()->getTags(), static function (Tag $tag): bool {
            return 'method' === $tag->getName();
        });
        $addedMethodTags = array_map(function (ClassMethod $method) {
            return $this->createMethodTag($method);
        }, $addedMethods);

        // Remove method tags that we'll replace
        $initialMethodTags = array_udiff($initialMethodTags, $addedMethodTags, static function (Method $initial, Method $added): int {
            return strcmp($initial->getMethodName(), $added->getMethodName());
        });

        return array_merge($initialMethodTags, $addedMethodTags);
    }

    private function createMethodTag(ClassMethod $method): Method
    {
        /** @var array<int, array{name: string, type: Type}> $arguments */
        $arguments = array_map(function (Param $param): array {
            if (!($var = $param->var) instanceof Variable || !is_string($name = $var->name)) {
                throw new CodeFindingException('Cannot retrieve parameter variable name');
            }

            return [
                'name' => $name,
                'type' => $this->typeFormatter->asDocumentationType($param->type),
            ];
        }, $method->getParams());

        return new Method(
            $method->name->name,
            $arguments,
            $this->typeFormatter->asDocumentationType($method->getReturnType()),
            $method->isStatic()
        );
    }
}
