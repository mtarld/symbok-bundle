<?php

namespace Mtarld\SymbokBundle\Tests\Autoload;

use App\Entity\Product3;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Mtarld\SymbokBundle\Tests\KernelTestCase;

/**
 * @group doctrine
 * @group functional
 */
class DoctrineMetadataRefinerTest extends KernelTestCase
{
    public function testReplaceWithSymbokPath(): void
    {
        // Bundle is loaded (and therefore triggered path replacements, so we just have to do the checks)

        /** @var AnnotationDriver $annotationDriver */
        $annotationDriver = static::$container->get('doctrine.orm.default_annotation_metadata_driver');

        $this->assertEquals([
            realpath(__DIR__.'/../Fixtures/App/src/Entity'),
            realpath(__DIR__.'/../../var/cache/test/symbok/App/Entity'),
        ], $annotationDriver->getPaths());

        $this->assertEquals([
            realpath(__DIR__.'/../Fixtures/App/src/Entity'),
        ], $annotationDriver->getExcludePaths());


        $this->assertEquals([
            Product3::class,
        ], $annotationDriver->getAllClassNames());
    }
}
