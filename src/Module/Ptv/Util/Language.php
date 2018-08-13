<?php

namespace App\Module\Ptv\Util;

class Language
{
    public static function isAllowed($lang) : bool
    {
        return in_array($lang, ['fi', 'en', 'sv'], true);
    }
}
