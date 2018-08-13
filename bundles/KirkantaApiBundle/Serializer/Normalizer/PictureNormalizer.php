<?php

namespace KirjastotFi\KirkantaApiBundle\Serializer\Normalizer;

use App\Entity\Picture;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PictureNormalizer implements NormalizerInterface
{
    private $inner;

    public function __construct(NormalizerInterface $inner)
    {
        $this->inner = $inner;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Picture;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $values = $this->inner->normalize($object, $format, $context);
        $meta = $object->getMeta();

        foreach ($object->getSizes() as $size) {
            $values['files'][$size] = $this->toUrl($object->getFilename(), $size);

            if (isset($meta['resolution'][$size])) {
                $values['sizes'][$size] = $meta['resolution'][$size];
            }
        }

        return $values;
    }

    private function toUrl(string $filename, string $size) : string
    {
        $protocol = 'https://';
        $domain = 'kirkanta.kirjastot.fi';
        $path = implode('/', ['/files/images', $size, $filename]);

        return $protocol . $domain . $path;
    }
}
