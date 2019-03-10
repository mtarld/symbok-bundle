<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Property;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\Column;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Property\PropertyAnnotation;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Property\PropertyTypes;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use PhpParser\Node\Stmt\Property as ClassProperty;

class TypesParser
{
    /** @var array */
    private $doctrineTypesMap;

    public function __construct()
    {
        // https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/types.html
        $this->doctrineTypesMap = [
        Type::SMALLINT => new Integer(),
        Type::INTEGER => new Integer(),
        Type::BIGINT => new String_(),
        Type::DECIMAL => new String_(),
        Type::FLOAT => new Float_(),
        Type::STRING => new String_(),
        Type::TEXT => new String_(),
        Type::GUID => new String_(),

        // Resource type (not supported yet)
//        Type::BINARY => null,
//        Type::BLOB => null,

        Type::BOOLEAN => new Boolean(),
        Type::DATE => new Object_(new Fqsen('\\' . DateTime::class)),
        Type::DATE_IMMUTABLE => new Object_(new Fqsen('\\' . DateTimeImmutable::class)),
        Type::DATETIME => new Object_(new Fqsen('\\' . DateTime::class)),
        Type::DATETIME_IMMUTABLE => new Object_(new Fqsen('\\' . DateTimeImmutable::class)),
        Type::DATETIMETZ => new Object_(new Fqsen('\\' . DateTime::class)),
        Type::DATETIMETZ_IMMUTABLE => new Object_(new Fqsen('\\' . DateTimeImmutable::class)),
        Type::TIME => new Object_(new Fqsen('\\' . DateTime::class)),
        Type::TIME_IMMUTABLE => new Object_(new Fqsen('\\' . DateTimeImmutable::class)),
        Type::DATEINTERVAL => new Object_(new Fqsen('\\' . DateInterval::class)),
        Type::TARRAY => new Array_(),
        Type::SIMPLE_ARRAY => new Array_(),
        Type::JSON_ARRAY => new Array_(),
        Type::JSON => new Array_(),
        Type::OBJECT => new Object_()
    ];
    }

    public function parse(ClassProperty $property, array $annotations, Context $context): PropertyTypes
    {
        // First, search for @var (higher priority)
        $propertyDocBlock = $this->getDocBlock($property, $context);
        $varTags = $propertyDocBlock->getTagsByName('var');
        if (sizeof($varTags)) {
            $baseType = $varTags[0]->getType();
            return new PropertyTypes($baseType, null);
        }

        // Then, search for Doctrine relation annotation
        /** @var PropertyAnnotation $doctrineRelation */
        $doctrineRelation = $annotations['relation'];
        if ($doctrineRelation) {
            if ($doctrineRelation->isDoctrineCollectionAnnotation()) {
                $collectionFqsen = new Fqsen('\\' . Collection::class);
                $targetEntity = $doctrineRelation->getRealAnnotation()->targetEntity;
                $entityFqsen = null;
                if ($targetEntity) {
                    $entityFqsen = new Fqsen('\\' . $targetEntity);
                }

                $baseType = new Object_($collectionFqsen);
                $relationType = $entityFqsen ? new Object_($entityFqsen) : null;

                return new PropertyTypes($baseType, $relationType);
            }

            if ($doctrineRelation->isDoctrineEntityAnnotation()) {
                $targetEntity = $doctrineRelation->getRealAnnotation()->targetEntity;
                if ($targetEntity) {
                    $entityFqsen = new Fqsen('\\' . $targetEntity);

                    return new PropertyTypes(new Object_($entityFqsen), null);
                }
            }

        }

        // If no Doctrine relation annotation found, search for Doctrine column annotation
        /** @var PropertyAnnotation $doctrineColumn */
        $doctrineColumn = $annotations['column'];
        if ($doctrineColumn) {
            /** @var Column $realAnnotation */
            $realAnnotation = $doctrineColumn->getRealAnnotation();
            $doctrineType = Type::getType($realAnnotation->type);
            if (array_key_exists($doctrineType->getName(), $this->doctrineTypesMap)) {
                $baseType = $this->doctrineTypesMap[$doctrineType->getName()];
                return new PropertyTypes($baseType, null);
            }
        }

        // If nothing found, return mixed type
        $baseType = isset($baseType) ? $baseType : new Mixed_();

        return new PropertyTypes($baseType, null);
    }

    private function getDocBlock(ClassProperty $property, Context $context): DocBlock
    {
        return DocBlockFactory::createInstance()->create(
            (string)$property->getDocComment(),
            $context
        );
    }
}
