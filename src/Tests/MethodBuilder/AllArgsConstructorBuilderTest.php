<?php

namespace Mtarld\SymbokBundle\Tests\MethodBuilder;

use Mtarld\SymbokBundle\Behavior\AllArgsConstructorBehavior;
use Mtarld\SymbokBundle\MethodBuilder\AllArgsConstructorBuilder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group builder
 */
class AllArgsConstructorBuilderTest extends TestCase
{
    public function testBuildExpectedContent(): void
    {
        $property1 = $this->createMock(SymbokProperty::class);
        $property1
            ->method('getName')
            ->willReturn('p1')
        ;

        $property2 = $this->createMock(SymbokProperty::class);
        $property2
            ->method('getName')
            ->willReturn('p2')
        ;

        $class = $this->createMock(SymbokClass::class);
        $class
            ->method('getProperties')
            ->willReturn([
                $property1,
                $property2,
            ])
        ;

        $behavior = $this->createMock(AllArgsConstructorBehavior::class);
        $behavior
            ->expects($this->at(0))
            ->method('isNullable')
            ->willReturn(true)
        ;

        $behavior
            ->expects($this->at(1))
            ->method('isNullable')
            ->willReturn(false)
        ;

        $typeFormatter = $this->createMock(TypeFormatter::class);
        $typeFormatter
            ->expects($this->at(0))
            ->method('asPhpString')
            ->willReturn('?int')
        ;

        $typeFormatter
            ->expects($this->at(1))
            ->method('asPhpString')
            ->willReturn('string')
        ;

        $builder = new AllArgsConstructorBuilder(
            $behavior,
            $typeFormatter
        );
        $method = $builder->build($class);
        $methodCode = (new Standard())->prettyPrint([$method]);

        $this->assertSame(
            'public function __construct(?int $p1 = null, string $p2)
{
    $this->p1 = $p1;
    $this->p2 = $p2;
}',
            $methodCode
        );
    }
}
