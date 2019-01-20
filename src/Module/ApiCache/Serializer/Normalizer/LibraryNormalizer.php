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

        return $values;
    }
}
