<?php

namespace App\Util;

use App\I18n\StringMap;

class PeriodSections extends StringMap
{
    public function __construct()
    {
        parent::__construct([
            'Regular timetables' => 'default',
            'Self service' => 'selfservice',
            'Reading room' => 'magazines',
        ]);
    }
}
