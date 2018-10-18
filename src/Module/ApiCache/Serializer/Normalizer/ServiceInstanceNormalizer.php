<?php

namespace App\Module\ApiCache\Serializer\Normalizer;

use App\Entity\ServiceInstance;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ServiceInstanceNormalizer implements NormalizerInterface
{
    private $inner;

    public function __construct(NormalizerInterface $inner)
    {
        $this->inner = $inner;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ServiceInstance;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $values = $this->inner->normalize($object, $format, $context);
        $template = $values['template'];
        $values['id'] = $template['id'];
        $values['standardName'] = $template['name'];

        unset($values['template']);

        return $values;
    }
}
