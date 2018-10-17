<?php

namespace App\Module\ApiCache\Serializer\Normalizer;

use App\Entity\Consortium;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConsortiumNormalizer implements NormalizerInterface
{
    private $inner;

    public function __construct(NormalizerInterface $inner)
    {
        $this->inner = $inner;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Consortium;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $values = $this->inner->normalize($object, $format, $context);
        $values['logo'] = $values['logo']['files'] ?? null;
        return $values;
    }
}
