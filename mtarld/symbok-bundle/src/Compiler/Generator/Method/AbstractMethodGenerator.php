<?php

namespace Mtarld\SymbokBundle\Compiler\Generator\Method;

use Mtarld\SymbokBundle\Compiler\Statements;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\ClassMethod;

abstract class AbstractMethodGenerator
{
    /** @var Context */
    protected $context;

    /** @var Serializer */
    private $docBlockSerializer;

    public function __construct(Context $context)
    {
        $this->context = $context;
        $this->docBlockSerializer = new Serializer();
    }

    public final function generate(): Statements
    {
        $statements = new Statements();
        $statements->add(
            new ClassMethod(
                $this->getName(),
                [
                    'flags' => $this->getFlags(),
                    'params' => $this->getParams(),
                    'stmts' => $this->getStmts(),
                    'returnType' => $this->getReturnType(),
                ],
                [
                    'comments' => $this->getComments()
                ]
            )
        );

        return $statements;
    }

    abstract protected function getName(): string;

    abstract protected function getFlags(): int;

    abstract protected function getParams(): array;

    abstract protected function getStmts(): array;

    abstract protected function getReturnType(): ?string;

    protected final function getComments(): array
    {
        $comments = [];
        foreach ($this->getCommentDocBlocks() as $docBlock) {
            /** @var DocBlock $docBlock */
            $comments[] = $this->createComment($docBlock);
        }

        return $comments;
    }

    abstract protected function getCommentDocBlocks(): array;

    protected function createComment(DocBlock $docblock): Comment
    {
        return new Doc(
            $this->docBlockSerializer->getDocComment($docblock)
        );
    }
}
