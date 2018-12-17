<?php

namespace App\Util;

use App\I18n\StringMap;

class LibraryTypes extends StringMap
{
    public function __construct()
    {
        parent::__construct([
            'Municipal library' => 'municipal',
            'Mobile library' => 'mobile',
            'Home service' => 'home_service',
            'Institutional library' => 'institutional',
            'Children\'s library' => 'children',
            'Music library' => 'music',
            'Special library' => 'special',
            'Vocational college library' => 'vocational_college',
            'School library' => 'school',
            'Polytechnic library' => 'polytechnic',
            'University library' => 'university',
            'Other library organisation' => 'other',
        ]);
    }
}
