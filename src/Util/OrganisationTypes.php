<?php

namespace App\Util;

use App\I18n\StringMap;

class OrganisationTypes extends StringMap
{
    public function __construct()
    {
        parent::__construct([
            'Library' => 'library',
            'Mobile library stop' => 'mobile_stop',
            'Department' => 'department',
            'Concentrated service' => 'centralized_service',
            'Library facility' => 'facility',
            'Archive' => 'archive',
            'Museum' => 'museum',
            'Other organisation' => 'other',
        ]);
    }
}
