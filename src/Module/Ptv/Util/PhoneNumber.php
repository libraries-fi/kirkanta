<?php

namespace App\Module\Ptv\Util;

class PhoneNumber
{
    public static function normalize(string $number) : string
    {
        return preg_replace('/\D/', '', $number);
    }
}
