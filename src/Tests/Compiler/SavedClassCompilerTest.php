<?php

namespace Mtarld\SymbokBundle\Tests\Compiler;

use Mtarld\SymbokBundle\Compiler\RuntimeClassCompiler;
use Mtarld\SymbokBundle\Compiler\SavedClassCompiler;
use Mtarld\SymbokBundle\Exception\CodeFindingException;
use Mtarld\SymbokBundle\Factory\ClassFactory;
use Mtarld\SymbokBundle\Finder\PhpCodeFinder;
use Mtarld\SymbokBundle\Model\SymbokClass;
use Mtarld\SymbokBundle\Util\TypeFormatter;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Nullable;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group unit
 * @group compiler
 */
class SavedClassCompilerTest extends TestCase
{
    /**
     * @testdox Creating method tag keep all needed data
     */
    public function testCreateMethodTag(): void
    {
        $compiler = new SavedClassCompiler(
            $this->createMock(ClassFactory::class),
            $this->createMock(RuntimeClassCompiler::class),
            $this->createMock(PhpCodeFinder::class),
            new TypeFormatter()
        );

        $reflection = new ReflectionClass(get_class($compiler));
        $method = $reflection->getMethod('createMethodTag');
        $method->setAccessible(true);

        $classMethod = new ClassMethod('a', [
            'params' => [
                new Param(new Variable('p1'), null, new NullableType(new Identifier('int'))),
                new Param(new Variable('p2'), null, new Identifier('string')),
                new Param(new Variable('p3'), null, null),
                new Param(new Variable('p4'), null, new NullableType('array')),
            ],
            'returnType' => new Nullable(new Boolean()),
        ]);

        $methodTag = $method->invokeArgs($compiler, [$classMethod]);

        $this->assertSame($classMethod->name->name, $methodTag->getMethodName());

        $this->assertSame(Compound::class, get_class($methodTag->getReturnType()));

        $methodTagArgumentNames = array_map(static function (array $argument): string {
            return $argument['name'];
        }, $methodTag->getArguments());

        $this->assertContains('p1', $methodTagArgumentNames);
        $this->assertContains('p2', $methodTagArgumentNames);
        $this->assertContains('p3', $methodTagArgumentNames);
        $this->assertContains('p4', $methodTagArgumentNames);

        $methodTagArgumentTypes = array_map(static function (array $argument): string {
            return (string) $argument['type'];
        }, $methodTag->getArguments());

        $this->assertContains('int|null', $methodTagArgumentTypes);
        $this->assertContains('string', $methodTagArgumentTypes);
        $this->assertContains('mixed', $methodTagArgumentTypes);
        $this->assertContains('array|null', $methodTagArgumentTypes);
    }

    /**
     * @testdox Updated method tags contains overriden, old and new method tags
     */
    public function testGetUpdatedMethodTags(): void
    {
        $finder = $this->createMock(PhpCodeFinder::class);
        $finder
            ->expects($this->at(0))
            ->method('findMethods')
            ->willReturn([
                new ClassMethod('a'),
            ])
        ;

        $finder
            ->expects($this->at(1))
            ->method('findMethods')
            ->willReturn([
                new ClassMethod('a'),
                new ClassMethod('b'),
                new ClassMethod('c'),
            ])
        ;

        $compiler = new SavedClassCompiler(
            $this->createMock(ClassFactory::class),
            $this->createMock(RuntimeClassCompiler::class),
            $finder,
            new TypeFormatter()
        );

        $reflection = new ReflectionClass(get_class($compiler));
        $method = $reflection->getMethod('getUpdatedMethodTags');
        $method->setAccessible(true);

        $initialClassDocBlock = new DocBlock('', null, [new Method('c', ['oldArg'])]);

        $initialClass = $this->createMock(SymbokClass::class);
        $initialClass
            ->method('getStatements')
            ->willReturn([])
        ;
        $initialClass
            ->method('getDocBlock')
            ->willReturn($initialClassDocBlock)
        ;

        $runtimeClass = $this->createMock(SymbokClass::class);
        $runtimeClass
            ->method('getStatements')
            ->willReturn([])
        ;

        $methodTags = $method->invokeArgs($compiler, [$initialClass, $runtimeClass]);

        $methodTagNames = array_map(function (Method $method) {
            return $method->getMethodName();
        }, $methodTags);

        $this->assertContains('b', $methodTagNames);
        $this->assertContains('c', $methodTagNames);
        $this->assertNotContains('a', $methodTagNames);

        foreach ($methodTags as $methodTag) {
            $this->assertSame([], $methodTag->getArguments());
        }
    }

    /**
     * @testdox Updated doc block keep all needed data plus updated method tags
     */
    public function testGetUpdatedDocBlock(): void
    {
        $finder = $this->createMock(PhpCodeFinder::class);
        $finder
            ->expects($this->at(0))
            ->method('findMethods')
            ->willReturn([])
        ;

        $finder
            ->expects($this->at(1))
            ->method('findMethods')
            ->willReturn([
                new ClassMethod('a'),
            ])
        ;

        $compiler = new SavedClassCompiler(
            $this->createMock(ClassFactory::class),
            $this->createMock(RuntimeClassCompiler::class),
            $finder,
            new TypeFormatter()
        );

        $reflection = new ReflectionClass(get_class($compiler));
        $method = $reflection->getMethod('getUpdatedDocBlock');
        $method->setAccessible(true);

        $initialClassDocBlock = new DocBlock('', null, [
            new Method('b', ['oldArg']),
            new Author('mtarld', 'm.tarld@email.com'),
        ]);

        $initialClass = $this->createMock(SymbokClass::class);
        $initialClass
            ->method('getStatements')
            ->willReturn([])
        ;
        $initialClass
            ->method('getDocBlock')
            ->willReturn($initialClassDocBlock)
        ;

        $runtimeClass = $this->createMock(SymbokClass::class);
        $runtimeClass
            ->method('getStatements')
            ->willReturn([])
        ;

        $docBlock = $method->invokeArgs($compiler, [$initialClass, $runtimeClass]);

        $tagClasses = array_map(function (Tag $tag) {
            return get_class($tag);
        }, $docBlock->getTags());

        $this->assertContains(Method::class, $tagClasses);
        $this->assertContains(Author::class, $tagClasses);
    }

    /**
     * @testdox Compilation replaces old doc block by the new one
     */
    public function testCompileIsReplacingDocblock(): void
    {
        $initialDocBlock = new DocBlock('', null, [new Method('a', ['oldArg'])]);
        $class = new SymbokClass('foo', [], $initialDocBlock, [], [], new Context('baz'));

        $classFactory = $this->createMock(ClassFactory::class);
        $classFactory
            ->method('create')
            ->willReturn($class)
        ;

        $finder = $this->createMock(PhpCodeFinder::class);

        $finder
            ->expects($this->at(1))
            ->method('findMethods')
            ->willReturn([
                new ClassMethod('a'),
                new ClassMethod('b'),
                new ClassMethod('c'),
            ])
        ;

        $compiler = new SavedClassCompiler(
            $classFactory,
            $this->createMock(RuntimeClassCompiler::class),
            $finder,
            new TypeFormatter()
        );

        $result = $compiler->compile([]);

        $methodTags = array_filter($result->getDocBlock()->getTags(), function (Tag $tag) {
            return $tag instanceof Method;
        });

        foreach ($methodTags as $methodTag) {
            $this->assertCount(0, $methodTag->getArguments());
        }
    }

    public function testCreateMethodTagExceptionWhenWrongParamVariable(): void
    {
        $compiler = new SavedClassCompiler(
            $this->createMock(ClassFactory::class),
            $this->createMock(RuntimeClassCompiler::class),
            $this->createMock(PhpCodeFinder::class),
            $this->createMock(TypeFormatter::class)
        );

        $reflection = new ReflectionClass(get_class($compiler));
        $method = $reflection->getMethod('createMethodTag');
        $method->setAccessible(true);

        /** @psalm-suppress NullArgument */
        $classMethod = new ClassMethod('a', [
            'params' => [
                new Param(null),
            ],
        ]);

        $this->expectException(CodeFindingException::class);
        $this->expectExceptionMessage('Cannot retrieve parameter variable');
        $method->invokeArgs($compiler, [$classMethod]);
    }
}
