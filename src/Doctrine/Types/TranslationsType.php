<?php

namespace App\Doctrine\Types;

use Doctrine\DBAL\Types\JsonArrayType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use App\I18n\Translations;

class TranslationsType extends JsonArrayType
{
    public function getName() : string
    {
        return 'translations';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $data = parent::convertToPHPValue($value, $platform);
        return new Translations($data);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return parent::convertToDatabaseValue($value->getData(), $platform);
    }
}
