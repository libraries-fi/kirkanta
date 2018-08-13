<?php

namespace KirjastotFi\KirkantaApiBundle;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;
use Symfony\Component\Serializer\Serializer;

use KirjastotFi\KirkantaApiBundle\Serializer\Normalizer\EntityNormalizer;
use App\Entity\Address;
use App\Entity\City;
use App\Entity\Organisation;

class StandardSerializerFactory
{
    public static function create()
    {
        $file = '../src/KirjastotFi/KirkantaApiBundle/Resources/config/serializer/standard.yml';
        $metadata = new ClassMetadataFactory(new YamlFileLoader($file));

        $normalizers = [
            'dt' => new DateTimeNormalizer,
            'entity' => new EntityNormalizer($metadata),
        ];

        $normalizers['entity']->setOverride(Organisation::class, 'parent', [__CLASS__, 'entityId']);
        $normalizers['entity']->setOverride(Address::class, 'city', [__CLASS__, 'cityName']);
        $normalizers['entity']->setOverride(Address::class, 'coordinates', [__CLASS__, 'splitCoordinates']);

        $encoders = [
            new JsonEncoder(new JsonEncode(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
        ];

        return new Serializer($normalizers, $encoders);
    }

    public static function entityId($entity) : ?int
    {
        return $entity ? $entity->getId() : null;
    }

    public static function cityName(?City $city) : ?string
    {
        return $city ? $city->getName() : null;
    }

    public function splitCoordinates(?string $coordinates) : ?array
    {
        if ($coordinates) {
            list($lat, $lon) = explode(',', $coordinates);
            return [
                'lat' => (float)trim($lat),
                'lon' => (float)trim($lon),
            ];
        }
        return null;
    }
}
