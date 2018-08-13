<?php

namespace KirjastotFi\KirkantaApiBundle\Serializer\Normalizer;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Does additional processing to ensure that Collections are properly serialized as XML.
 */
class CollectionNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, $format = null) : bool
    {
        return $format == 'xml' && $data instanceof Collection;
    }

    public function normalize($collection, $format = null, array $context = [])
    {
        $normalized = [];

        foreach ($collection as $key => $row) {
            if ($format == 'xml') {
                $type_key = $this->getBaseClassName($row) ?? 'item';
                $normalized['@type'] = 'collection';
                $normalized['@size'] = count($collection);
                $normalized[$type_key][] = [
                    // '@id' => $row->getId(),
                    '#' => $row,
                ];
            } else {
                $normalized[] = $row;
            }
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
