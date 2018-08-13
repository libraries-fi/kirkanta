<?php

namespace App\Module\Ptv\Util;

class Address
{
    public static function parseStreetAndNumber($address) : array
    {
        $parts = explode(' ', $address);
        $street_nr = end($parts);

        if (ctype_digit($street_nr)) {
            $street = implode(' ', array_slice($parts, 0, -1));
            return [$street, (int)$street_nr];
        } else {
            return [$address, null];
        }
    }
}
