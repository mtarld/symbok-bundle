<?php

namespace Mtarld\SymbokBundle\Tests\Functional\Context;

use Mtarld\SymbokBundle\Context\ContextHolder;
use Mtarld\SymbokBundle\Exception\SymbokException;
use Mtarld\SymbokBundle\Helper\NodesFinder;
use Mtarld\SymbokBundle\Tests\Functional\AbstractFunctionalTest;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\ParserFactory;

class ContextHolderTest extends AbstractFunctionalTest
{
    private $filePath = __DIR__ . '/../../Fixtures/files/Product1.php';

    public function testGetContext()
    {
        $context = $this->getContext();
        $this->assertInstanceOf(Context::class, $context);
    }

    private function getContext()
    {
        $phpParser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $nodes = $phpParser->parse(file_get_contents($this->filePath));

        $namespace = NodesFinder::findNamespace(...$nodes);
        $uses = NodesFinder::findUses(...$namespace->stmts);

        $contextHolder = self::$container->get(ContextHolder::class);
        $contextHolder->buildContext((string)$namespace->name, $uses);

        return $contextHolder->getContext();
    }

    public function testContextAliases()
    {
        $context = $this->getContext();

        $aliases = $context->getNamespaceAliases();
        $this->assertArrayHasKey('ORM', $aliases);
        $this->assertArrayNotHasKey(0, $aliases);
        $this->assertSame('Doctrine\\ORM\\Mapping', $aliases['ORM']);
    }

    public function testGetContextNotLoaded()
    {
        try {
            /** @var ContextHolder $contextHolder */
            $contextHolder = self::$container->get(ContextHolder::class);
            $contextHolder->getContext();
        } catch (SymbokException $e) {
            $this->assertSame('Type context was not built', $e->getMessage());
        }
    }
}
