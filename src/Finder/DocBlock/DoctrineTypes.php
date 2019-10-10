<?php

namespace Mtarld\SymbokBundle\Finder\DocBlock;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\ORM\Version;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;

class DoctrineTypes
{
    /**
     * @codeCoverageIgnore
     */
    public function getTypeMap(): array
    {
        // https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/types.html
        $doctrineTypesMap = [
            DoctrineType::SMALLINT => new Integer(),
            DoctrineType::INTEGER => new Integer(),
            DoctrineType::BIGINT => new String_(),
            DoctrineType::DECIMAL => new String_(),
            DoctrineType::FLOAT => new Float_(),
            DoctrineType::STRING => new String_(),
            DoctrineType::TEXT => new String_(),
            DoctrineType::GUID => new String_(),
            DoctrineType::BOOLEAN => new Boolean(),
            DoctrineType::DATE => new Object_(new Fqsen('\\'.DateTime::class)),
            DoctrineType::DATETIME => new Object_(new Fqsen('\\'.DateTime::class)),
            DoctrineType::DATETIMETZ => new Object_(new Fqsen('\\'.DateTime::class)),
            DoctrineType::TIME => new Object_(new Fqsen('\\'.DateTime::class)),
            DoctrineType::TARRAY => new Array_(),
            DoctrineType::SIMPLE_ARRAY => new Array_(),
            DoctrineType::JSON_ARRAY => new Array_(),
            DoctrineType::OBJECT => new Object_(),
            DoctrineType::BINARY => null,
            DoctrineType::BLOB => null,
        ];

        // 2.6+ compatibility
        if (Version::compare('2.6') < 0) {
            $doctrineTypesMap = array_merge($doctrineTypesMap, [
                DoctrineType::DATE_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
                DoctrineType::DATETIME_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
                DoctrineType::DATETIMETZ_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
                DoctrineType::TIME_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
                DoctrineType::DATEINTERVAL => new Object_(new Fqsen('\\'.DateInterval::class)),
                DoctrineType::JSON => new Array_(),
            ]);
        }

        return $doctrineTypesMap;
    }
}
