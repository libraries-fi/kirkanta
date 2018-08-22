<?php

namespace App\Util;

use App\I18n\StringMap;

class ServicePointTypes extends StringMap
{
    public function __construct()
    {
        parent::__construct([
            'Archive' => 'archive',
            'Museum' => 'museum',
            'Other' => 'other',
        ]);
    }
}
