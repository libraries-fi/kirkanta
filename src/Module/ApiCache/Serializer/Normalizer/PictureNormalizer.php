<?php

namespace App\Module\ApiCache\Serializer\Normalizer;

use App\Entity\Picture;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

class PictureNormalizer implements NormalizerInterface
{
    private $inner;
    private $mappings;

    const BASE_URL_PREFIX = 'https://kirkanta.kirjastot.fi';

    public function __construct(NormalizerInterface $inner, PropertyMappingFactory $mappings)
    {
        $this->inner = $inner;
        $this->mappings = $mappings;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Picture;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $values = $this->inner->normalize($object, $format, $context);
        $meta = $object->getMeta();
        $basedir = $this->mappings->fromObject($object)[0]->getUriPrefix();

        foreach ($object->getSizes() as $size) {
            // NOTE: $basedir starts with a slash, hence BASE_URL_PREFIX is not in the array too.
            $values['files'][$size]['url'] = self::BASE_URL_PREFIX . implode('/', [$basedir, $size, $object->getFilename()]);

            if (isset($meta[$size])) {
                $values['files'][$size]['resolution'] = implode('x', $meta[$size]['dimensions']);
                $values['files'][$size]['size'] = $meta[$size]['filesize'];
            }
        }

        return $values;
    }
}
