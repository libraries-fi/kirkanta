<?php

namespace App\Util;

use App\I18n\StringMap;

class DepartmentTypes extends StringMap
{
    public function __construct()
    {
        parent::__construct([
            'Department' => 'department',
            'Meta' => 'meta',
            'Mobile stop' => 'mobile_stop'
        ]);
    }
}
