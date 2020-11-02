<?php

namespace App\Module\ApiCache\Serializer\Normalizer;

use stdClass;
use App\Entity\Feature\StateAwareness;
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

        usort($sortedPictures, function ($a, $b) {
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

        if (isset($values['customData'])) {
            $values['customData'] = $this->extractCustomData($object->getCustomData());
        }

        if (empty($values['mailAddress']['zipcode']) && empty($values['mailAddress']['boxNumber'])) {
            $values['mailAddress'] = null;
        } else {
            unset($values['mailAddress']['city']);
            unset($values['mailAddress']['coordinates']);
        }

        $values['librarySystem'] = [
            'name' => $object->getLibrarySystemName(),
            'servicepoint_id' => $object->getLibrarySystemServicepointId()
        ];

        if (empty($values['librarySystem']['name']) && empty($values['librarySystem']['servicepoint_id'])) {
            $values['librarySystem'] = null;
        }

        $this->processPersons($values);
        $this->buildBuildingInfo($values);

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
                if (strlen($value) === 0) {
                    $entry['value']->$langcode = $fallback;
                }
            }

            // Cast to associative array for serialization
            $entry['value'] = (array) $entry['value'];
            $entry['title'] = (array) $entry['title'];

            $entries[] = $entry;
        }

        return $entries;
    }

    private function convertLegacyData(stdClass $legacyItem) : stdClass
    {
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

    private function buildBuildingInfo(array &$document) : array
    {
        $document['buildingInfo'] = [
            'buildingName' => $document['buildingName'],
            'architect' => $document['buildingArchitect'],
            'interiorDesigner' => $document['interiorDesigner'],
            'constructionYear' => $document['constructionYear'] ?: null,
        ];

        unset($document['buildingName'], $document['buildingArchitect'], $document['interiorDesigner'], $document['constructionYear']);

        return $document;
    }

    private function processPersons(array &$document) : array
    {
        if (isset($document['persons'])) {
            $document['persons'] = array_filter($document['persons'], function ($person) {
                return $person['state'] == StateAwareness::PUBLISHED;
            });

            usort($document['persons'], function ($a, $b) {
                return strcasecmp("{$a['firstName']} {$a['lastName']}", "{$b['firstName']} {$b['lastName']}");
            });
        }

        return $document;
    }
}
