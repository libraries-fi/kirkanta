<?php

namespace KirjastotFi\KirkantaApiBundle\Serializer\Normalizer;

use App\Entity\Person;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PersonNormalizer implements NormalizerInterface
{
    private $inner;

    public function __construct(NormalizerInterface $inner)
    {
        $this->inner = $inner;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Person;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $values = $this->inner->normalize($object, $format, $context);

        if (!$object->isEmailPublic()) {
            $values['email'] = null;
        }

        return $values;
    }
}
