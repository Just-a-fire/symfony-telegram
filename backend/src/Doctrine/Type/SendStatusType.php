<?php

namespace App\Doctrine\Type;

use App\Enum\SendStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class SendStatusType extends Type
{
    public const NAME = 'send_status_enum';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'send_status'; // Имя типа в PostgreSQL
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof SendStatus ? $value->value : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return $value !== null ? SendStatus::from($value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
