<?php

namespace App\Module\ApiCache\Serializer\Normalizer;

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
        $context['for_finna_organisation'] = true;

        $values = $this->inner->normalize($object, $format, $context);
        $values += $values['consortium'];
        unset($values['consortium']);

        if (isset($values['customData'])) {
            $values['customData'] = $this->extractCustomData($object->getCustomData());
        }

        return $values;
    }

    private function extractCustomData(array $customData)
    {
        $entries = [];

        foreach ($customData as $item) {
            $entry = get_object_vars($item);
            $fallback = null;

            foreach ($item->value as $value) {
                if (strlen($value) > 0) {
                    $fallback = $value;
                    break;
                }
            }

            foreach ($entry['value'] as $langcode => $value) {
                if (strlen($value) === 0) {
                    $entry['value']->$langcode = $fallback;
                }
            }

            $entries[] = $entry;
        }

        return $entries;
    }
}
