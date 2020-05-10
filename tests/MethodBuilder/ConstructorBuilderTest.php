<?php

namespace Mtarld\SymbokBundle\Tests\MethodBuilder;

use Mtarld\SymbokBundle\MethodBuilder\ConstructorBuilder;
use Mtarld\SymbokBundle\Model\Relation\ManyToManyRelation;
use Mtarld\SymbokBundle\Model\Relation\OneToOneRelation;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group builder
 */
class ConstructorBuilderTest extends TestCase
{
    public function testBuildExpectedContent(): void
    {
        $property1 = $this->createMock(SymbokProperty::class);
        $property1
            ->method('getName')
            ->willReturn('p1')
        ;
        $property1
            ->method('getRelation')
            ->willReturn(new OneToOneRelation())
        ;

        $property2 = $this->createMock(SymbokProperty::class);
        $property2
            ->method('getName')
            ->willReturn('p2')
        ;
        $property2
            ->method('getRelation')
            ->willReturn(new ManyToManyRelation())
        ;

        $class = $this->createMock(SymbokClass::class);
        $class
            ->method('getProperties')
            ->willReturn([
                $property1,
                $property2,
            ])
        ;

        $method = (new ConstructorBuilder())->build($class);
        $methodCode = (new Standard())->prettyPrint([$method]);

        $this->assertSame(
            'public function __construct()
{
    $this->p2 = new Doctrine\Common\Collections\ArrayCollection();
}',
            $methodCode
        );
    }
}
