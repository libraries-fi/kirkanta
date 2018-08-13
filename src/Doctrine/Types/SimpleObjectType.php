<?php

namespace App\Doctrine\Types;

use Doctrine\DBAL\Types\JsonArrayType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use App\I18n\Translations;

class SimpleObjectType extends JsonArrayType
{
    public function getName() : string
    {
        return 'simple_object';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return json_decode($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
