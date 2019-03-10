<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Property;

use Doctrine\Common\Annotations\DocParser;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Property\PropertyAnnotation;
use Mtarld\SymbokBundle\Compiler\Code\Parser\ParserInterface;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt\Property as ClassProperty;

class AnnotationsParser implements ParserInterface
{
    /** @var DocParser */
    private $parser;

    public function __construct(DocParser $parser)
    {
        $this->parser = $parser;
    }

    public function parse($property, Context $context): array
    {
        /** @var ClassProperty $property */
        $parsedAnnotations = [];
        if ($property->getDocComment()) {
            $commentText = $property->getDocComment()->getText();
            $commentText = $this->replaceAnnotationsNamespace($commentText, $context);

            foreach ($this->parser->parse($commentText) as $annotation) {
                $parsedAnnotations[] = new PropertyAnnotation(
                    $annotation,
                    $this->getAnnotationType($annotation)
                );
            }
        }

        return [
            'all' => $parsedAnnotations,
            'column' => $this->getDoctrineColumnAnnotation($parsedAnnotations),
            'relation' => $this->getDoctrineRelationAnnotation($parsedAnnotations)
        ];
    }

    private function replaceAnnotationsNamespace(string $comment, Context $context): string
    {
        $ormAlias = null;
        $symbokAlias = null;
        foreach ($context->getNamespaceAliases() as $alias => $target) {
            if ($target == 'Doctrine\\ORM\\Mapping') {
                $ormAlias = is_int($alias) ? 'Mapping' : $alias;
            } elseif ($target == 'Mtarld\\SymbokBundle\\Annotation') {
                $symbokAlias = is_int($alias) ? 'Annotation' : $alias;
            }
        }

        $comment = str_replace('Doctrine\\ORM\\Mapping\\', '', $comment);
        if ($ormAlias) {
            $comment = str_replace("$ormAlias\\", '', $comment);
        }

        $comment = str_replace('Mtarld\\Symbok\\Annotation\\', '', $comment);
        if ($symbokAlias) {
            $comment = str_replace("$symbokAlias\\", '', $comment);
        }

        return $comment;
    }

    private function getAnnotationType($annotation): string
    {
        if ($this->isDoctrineCollectionAnnotation($annotation)) {
            return PropertyAnnotation::TYPE_DOCTRINE_COLLECTION;
        }

        if ($this->isDoctrineEntityAnnotation($annotation)) {
            return PropertyAnnotation::TYPE_DOCTRINE_ENTITY;
        }

        if ($this->isDoctrineColumnAnnotation($annotation)) {
            return PropertyAnnotation::TYPE_DOCTRINE_COLUMN;
        }

        return PropertyAnnotation::TYPE_STANDARD;
    }

    private function isDoctrineCollectionAnnotation($annotation): bool
    {
        $class = get_class($annotation);
        return $class == OneToMany::class || $class == ManyToMany::class;
    }

    private function isDoctrineEntityAnnotation($annotation): bool
    {
        $class = get_class($annotation);
        return $class == OneToOne::class || $class == ManyToOne::class;
    }

    private function isDoctrineColumnAnnotation($annotation): bool
    {
        $class = get_class($annotation);
        return $class == Column::class;
    }

    private function getDoctrineColumnAnnotation(array $annotations): ?PropertyAnnotation
    {
        foreach ($annotations as $annotation) {
            /** @var PropertyAnnotation $annotation */
            if ($annotation->isDoctrineColumnAnnotation()) {
                return $annotation;
            }
        }

        return null;
    }

    private function getDoctrineRelationAnnotation(array $annotations): ?PropertyAnnotation
    {
        foreach ($annotations as $annotation) {
            /** @var PropertyAnnotation $annotation */
            if ($annotation->isDoctrineRelationAnnotation()) {
                return $annotation;
            }
        }

        return null;
    }
}
