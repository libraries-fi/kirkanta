<?php

namespace App\Util;

use App\I18n\StringMap;

class SystemLanguages extends StringMap
{
    const DEFAULT_LANGCODE = 'fi';
    const TEMPORARY_LANGCODE = 'xx';

    public function __construct()
    {
        parent::__construct([
            'Finnish' => 'fi',
            'Swedish' => 'sv',
            'English' => 'en',
            'Russian' => 'ru',
            'Sami' => 'se',
        ]);
    }
}
