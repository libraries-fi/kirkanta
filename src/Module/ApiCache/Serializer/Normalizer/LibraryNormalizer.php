<?php

namespace App\Module\ApiCache\Serializer\Normalizer;

use App\Entity\LibraryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LibraryNormalizer implements NormalizerInterface
{
    private $inner;

    public function __construct(NormalizerInterface $inner)
    {
        $this->inner = $inner;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LibraryInterface;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $values = $this->inner->normalize($object, $format, $context);
        $values['coverPhoto'] = $values['pictures'][0]['files'] ?? null;
        $values['coordinates'] = $values['address']['coordinates'];
        unset($values['address']['coordinates']);
        return $values;
    }
}
