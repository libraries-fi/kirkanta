<?php

namespace App\Util;

use App\I18n\StringMap;

class ServiceTypes extends StringMap
{
    public function __construct()
    {
        parent::__construct([
            'Room' => 'room',
            'Hardware' => 'hardware',
            'Service' => 'service',
            'Network' => 'web_service',
            'Collection' => 'collection',
        ]);
    }
}
