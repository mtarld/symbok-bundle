<?php

namespace Mtarld\SymbokBundle\Tests\Autoload;

use Mtarld\SymbokBundle\Factory\DocFactory;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Visitor\ReplaceClassNodeVisitor;
use phpDocumentor\Reflection\DocBlock;
use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group replacer
 */
class ReplaceClassNodeVisitorTest extends TestCase
{
    public function testEnterNode(): void
    {
        $docFactory = $this->createMock(DocFactory::class);
        $docFactory
            ->method('createFromDocBlock')
            ->willReturn(new Doc('foo'))
        ;

        $class = $this->createMock(SymbokClass::class);
        $class
            ->method('getStatements')
            ->willReturn([
                'stmt',
            ])
        ;
        $class
            ->method('getDocBlock')
            ->willReturn(new DocBlock('docBlock'))
        ;

        $visitor = new ReplaceClassNodeVisitor($docFactory);
        $visitor->class = $class;

        $node = $visitor->enterNode(new Variable('bar'));
        $this->assertNull($node);

        $nodeClass = new Class_('class', [
            new Variable('var'),
        ]);

        /** @var Class_ $node */
        $node = $visitor->enterNode($nodeClass);

        $this->assertContains('stmt', $node->stmts);

        /** @var Doc $docComment */
        $docComment = $node->getDocComment();
        $this->assertSame('foo', $docComment->getText());
    }
}
