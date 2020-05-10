<?php

namespace Mtarld\SymbokBundle\Tests\MethodBuilder;

use Mtarld\SymbokBundle\Annotation\ToString;
use Mtarld\SymbokBundle\MethodBuilder\ToStringBuilder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group builder
 */
class ToStringBuilderTest extends TestCase
{
    public function testBuildExpectedContent(): void
    {
        $toStringAnnotation = new ToString();
        $toStringAnnotation->properties = ['p1', 'p2'];

        $class = $this->createMock(SymbokClass::class);
        $class
            ->method('getAnnotation')
            ->willReturn($toStringAnnotation)
        ;

        $method = (new ToStringBuilder())->build($class);
        $methodCode = (new Standard())->prettyPrint([$method]);

        $this->assertSame(
            'public function __toString() : string
{
    return (string) (\': \' . ($this->p1 . (\', \' . $this->p2)));
}',
            $methodCode
        );
    }
}
