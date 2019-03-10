<?php

namespace Mtarld\SymbokBundle\MethodGenerator\Class_;

use Mtarld\SymbokBundle\Context\ContextHolder;
use Mtarld\SymbokBundle\Model\Statements;
use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use phpDocumentor\Reflection\DocBlock\Serializer;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\ClassMethod;

abstract class AbstractClassMethodGenerator
{
    /** @var ContextHolder */
    protected $contextHolder;

    /** @var Serializer */
    private $docBlockSerializer;

    public function __construct(ContextHolder $contextHolder)
    {
        $this->contextHolder = $contextHolder;
        $this->docBlockSerializer = new Serializer();
    }

    final public function generate(SymbokClass $class): Statements
    {
        $statements = new Statements();
        $statements->add(
            new ClassMethod(
                $this->getName(),
                [
                    'flags'      => $this->getFlags(),
                    'params'     => $this->getParams($class),
                    'stmts'      => $this->getStmts($class),
                    'returnType' => $this->getReturnType(),
                ],
                [
                    'comments' => $this->getComments($class)
                ]
            )
        );

        return $statements;
    }

    abstract protected function getName(): string;

    abstract protected function getFlags(): int;

    abstract protected function getParams(SymbokClass $class): array;

    abstract protected function getStmts(SymbokClass $class): array;

    abstract protected function getReturnType(): ?string;

    private function getComments(SymbokClass $class): array
    {
        $comments = [];
        foreach ($this->getCommentDocBlocks($class) as $docBlock) {
            $comments[] = new Doc(
                $this->docBlockSerializer->getDocComment($docBlock)
            );
        }

        return $comments;
    }

    abstract protected function getCommentDocBlocks(SymbokClass $class): array;
}
