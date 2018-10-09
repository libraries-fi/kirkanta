<?php

namespace App\Serializer\Normalizer;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Does additional processing to ensure that Collections are properly serialized as XML.
 */
class OdmCollectionNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Collection && $format == 'json';
    }

    public function normalize($collection, $format = null, array $context = [])
    {
        if (!count($collection)) {
            return null;
        }

        $normalized = [
            '#type' => get_class($collection),
            'elements' => [],
        ];

        foreach ($collection as $key => $row) {
            $normalized['elements'][] = $row;
        }

        return $normalized;
    }

    private function getBaseClassName($object) : ?string
    {
        if (is_object($object)) {
          return strtolower(substr(strrchr(get_class($object), '\\'), 1));
        } else {
          return null;
        }
    }
}
