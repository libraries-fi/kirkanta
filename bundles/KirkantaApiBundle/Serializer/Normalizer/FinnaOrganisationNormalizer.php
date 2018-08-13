<?php

namespace KirjastotFi\KirkantaApiBundle\Serializer\Normalizer;

use App\Module\Finna\Entity\FinnaAdditions;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FinnaOrganisationNormalizer implements NormalizerInterface
{
    private $inner;

    public function __construct(NormalizerInterface $inner)
    {
        $this->inner = $inner;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof FinnaAdditions;
    }

    public function normalize($object, $format = null, array $context = []) : ?array
    {
        $values = $this->inner->normalize($object, $format, $context);
        $values += $values['consortium'];
        unset($values['consortium']);
        return $values;
    }
}
