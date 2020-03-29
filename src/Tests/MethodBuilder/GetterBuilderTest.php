<?php

namespace Mtarld\SymbokBundle\Tests\MethodBuilder;

use Mtarld\SymbokBundle\Behavior\GetterBehavior;
use Mtarld\SymbokBundle\MethodBuilder\GetterBuilder;
use Mtarld\SymbokBundle\Model\SymbokProperty;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\String_;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 * @group builder
 */
class GetterBuilderTest extends TestCase
{
    /**
     * @dataProvider buildExpectedContentDataProvider
     * @testdox Build expected content with $returnType return type and 'has' prefix $hasPrefix
     */
    public function testBuildExpectedContent(?string $returnType, ?Type $propertyType, bool $nullable, bool $hasPrefix, string $result): void
    {
        $property = $this->createMock(SymbokProperty::class);
        $property
            ->method('getName')
            ->willReturn('name')
        ;
        $property
            ->method('getType')
            ->willReturn($propertyType)
        ;

        $behavior = $this->createMock(GetterBehavior::class);
        $behavior
            ->method('hasHasPrefix')
            ->willReturn($hasPrefix)
        ;
        $behavior
            ->method('isNullable')
            ->willReturn($nullable)
        ;

        $typeFormatter = $this->createMock(TypeFormatter::class);
        $typeFormatter
            ->method('asPhpString')
            ->willReturn($returnType)
        ;

        $method = (new GetterBuilder($behavior, $typeFormatter))->build($property);
        $methodCode = (new Standard())->prettyPrint([$method]);

        $this->assertSame($result, $methodCode);
    }

    public function buildExpectedContentDataProvider(): iterable
    {
        yield [
            null,
            null,
            false,
            false,
            'public function getName()
{
    return $this->name;
}',
        ];

        yield [
            '?string',
            new String_(),
            true,
            false,
            'public function getName() : ?string
{
    return $this->name;
}',
        ];

        yield [
            'bool',
            new Boolean(),
            false,
            false,
            'public function isName() : bool
{
    return $this->name;
}',
        ];

        yield [
            'bool',
            new Boolean(),
            false,
            true,
            'public function hasName() : bool
{
    return $this->name;
}',
        ];
    }
}
