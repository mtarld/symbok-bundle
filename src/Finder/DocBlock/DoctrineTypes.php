<?php

namespace Mtarld\SymbokBundle\Finder\DocBlock;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Version;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;

class DoctrineTypes
{
    /**
     * @return array<Type|null>
     */
    public function getTypeMap(): array
    {
        return 0 <= Version::compare('2.6') ? $this->getDoctrineTypes() : $this->getDoctrineOldTypes();
    }

    private function getDoctrineTypes(): array
    {
        return [
            Types::SMALLINT => new Integer(),
            Types::INTEGER => new Integer(),
            Types::BIGINT => new String_(),
            Types::DECIMAL => new String_(),
            Types::FLOAT => new Float_(),
            Types::STRING => new String_(),
            Types::TEXT => new String_(),
            Types::GUID => new String_(),
            Types::BOOLEAN => new Boolean(),
            Types::DATE_MUTABLE => new Object_(new Fqsen('\\'.DateTime::class)),
            Types::DATE_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
            Types::DATETIME_MUTABLE => new Object_(new Fqsen('\\'.DateTime::class)),
            Types::DATETIME_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
            Types::DATETIMETZ_MUTABLE => new Object_(new Fqsen('\\'.DateTime::class)),
            Types::DATETIMETZ_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
            Types::TIME_MUTABLE => new Object_(new Fqsen('\\'.DateTime::class)),
            Types::TIME_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
            Types::DATEINTERVAL => new Object_(new Fqsen('\\'.\DateInterval::class)),
            Types::ARRAY => new Array_(),
            Types::SIMPLE_ARRAY => new Array_(),
            Types::JSON => new Array_(),
            Types::OBJECT => new Object_(),
            Types::BINARY => null,
            Types::BLOB => null,
        ];
    }

    /**
     * @psalm-suppress DeprecatedConstant
     */
    private function getDoctrineOldTypes(): array
    {
        return [
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
            DoctrineType::DATE_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
            DoctrineType::DATETIME => new Object_(new Fqsen('\\'.DateTime::class)),
            DoctrineType::DATETIME_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
            DoctrineType::DATETIMETZ => new Object_(new Fqsen('\\'.DateTime::class)),
            DoctrineType::DATETIMETZ_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
            DoctrineType::TIME => new Object_(new Fqsen('\\'.DateTime::class)),
            DoctrineType::TIME_IMMUTABLE => new Object_(new Fqsen('\\'.DateTimeImmutable::class)),
            DoctrineType::DATEINTERVAL => new Object_(new Fqsen('\\'.\DateInterval::class)),
            DoctrineType::TARRAY => new Array_(),
            DoctrineType::SIMPLE_ARRAY => new Array_(),
            DoctrineType::JSON => new Array_(),
            DoctrineType::JSON_ARRAY => new Array_(),
            DoctrineType::OBJECT => new Object_(),
            DoctrineType::BINARY => null,
            DoctrineType::BLOB => null,
        ];
    }
}
