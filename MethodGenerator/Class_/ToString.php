<?php

namespace Mtarld\SymbokBundle\MethodGenerator\Class_;

use Mtarld\SymbokBundle\Model\Symbok\SymbokClass;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\Types\String_;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Stmt\Class_;

class ToString extends AbstractClassMethodGenerator
{
    protected function getName(): string
    {
        return '__toString';
    }

    protected function getReturnType(): ?string
    {
        return 'string';
    }

    protected function getFlags(): int
    {
        return Class_::MODIFIER_PUBLIC;
    }

    protected function getParams(SymbokClass $class): array
    {
        return [];
    }


    protected function getStmts(SymbokClass $class): array
    {
        $stmts = [];

        $toStringProperties = $class->getRules()->getToStringProperties();
        $toStringValue = new Node\Expr\PropertyFetch(
            new Node\Expr\Variable('this'),
            $toStringProperties[0]
        );
        for ($i = 1; $i < sizeof($toStringProperties); $i++) {
            $toStringValue = new Concat(
                $toStringValue,
                new Concat(
                    new Node\Expr\ConstFetch(new Node\Name("', '")),
                    new Node\Expr\PropertyFetch(
                        new Node\Expr\Variable('this'),
                        $toStringProperties[$i]
                    )
                )
            );
        }

        $stmts[] = new Node\Stmt\Return_(
            new Node\Expr\Cast\String_(
                new Concat(
                    new Node\Expr\ConstFetch(new Node\Name("'{$class->getName()}: '")),
                    $toStringValue
                )
            )
        );

        return $stmts;
    }

    protected function getCommentDocBlocks(SymbokClass $class): array
    {
        $docBlock = new DocBlock(
            $class->getName() . ' toString.',
            null,
            [new Return_(new String_())],
            $this->contextHolder->getContext()
        );

        return [$docBlock];
    }
}
