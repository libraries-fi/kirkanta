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
        /**
         * Reset all weighted collections to ensure that entries are
         * ordered correctly after a possible re-order.
         */
        $object->getPictures()->setInitialized(false);
        $object->getPhoneNumbers()->setInitialized(false);
        $object->getLinks()->setInitialized(false);
        $object->getEmailAddresses()->setInitialized(false);

        $values = $this->inner->normalize($object, $format, $context);

        $sortedPictures = [];

        /**
         * Despite all sort of tricks and hacks, the pictures seem to not be sorted
         * properly in the source collection. So we resort to manually sorting them
         * here. Maybe this should then be applied to other sortable collections
         * as well but pictures are most critical due to the upfront nature of
         * the cover photo.
         */
        foreach ($object->getPictures() as $i => $picture) {
            $sortedPictures[] = [$picture->getWeight(), $values['pictures'][$i]];
        }

        usort($sortedPictures, function($a, $b) {
            return $a[0] - $b[0];
        });

        $values['pictures'] = array_column($sortedPictures, 1);
        $values['coverPhoto'] = $values['pictures'][0]['files'] ?? null;
        $values['coordinates'] = $values['address']['coordinates'];
        unset($values['address']['coordinates']);

        $values['primaryContactInfo'] = [
            'email' => $values['email'],
            'phone' => $values['phone'],
            'homepage' => $values['homepage'],
        ];

        unset($values['email'], $values['phone'], $values['homepage']);

        $values['transitInfo'] = [
            'buses' => $values['buses'] ?: null,
            'trams' => $values['trams'] ?: null,
            'trains' => $values['trains'] ?: null,
            'parking' => $values['parkingInstructions'],
            'directions' => $values['transitDirections'],
        ];

        unset($values['buses'], $values['trams'], $values['trains'], $values['parkingInstructions'], $values['transitDirections']);

        $values['customData'] = $this->extractCustomData($object->getCustomData());

        return $values;
    }

    private function extractCustomData(array $customData)
    {
        $entries = [];

        foreach ($customData as $item) {
            if (isset($item->translations)) {
                $item = $this->convertLegacyData($item);
            }

            $entry = get_object_vars($item);
            $fallback = null;

            foreach ($item->value as $value) {
                if (strlen($value) > 0) {
                    $fallback = $value;
                    break;
                }
            }

            foreach ($entry['value'] as $langcode => $value) {
                if (strlen($value) == 0) {
                    $entry['value']->{$langcode} = $fallback;
                }
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    private function convertLegacyData(\stdClass $legacyItem) {
        $item = (object)[
            'id' => $legacyItem->id,
            'title' => (object)['fi' => $legacyItem->title],
            'value' => (object)['fi' => $legacyItem->value],
        ];

        foreach ($legacyItem->translations as $langcode => $trdata) {
            $item->title->{$langcode} = $trdata->title;
            $item->value->{$langcode} = $trdata->value;
        }

        return $item;
    }
}
