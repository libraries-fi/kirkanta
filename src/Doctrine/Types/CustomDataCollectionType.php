<?php

namespace App\Doctrine\Types;

use App\Entity\CustomData;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class CustomDataCollectionType extends JsonType
{
    public function getName() : string
    {
        return 'custom_data_collection';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return json_decode($value);

        $entries = parent::convertToPHPValue($value, $platform);

        foreach ($entries as $i => $values) {
            $values['pos'] = $i + 1;
            $entries[$i] = CustomData::fromArray($values);
        }

        return new ArrayCollection($entries);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
