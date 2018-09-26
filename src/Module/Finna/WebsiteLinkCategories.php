<?php

namespace App\Module\Finna;

use App\I18n\StringMap;

class WebsiteLinkCategories extends StringMap
{
    public function __construct()
    {
        parent::__construct([
            'Finna materials' => 'finna_materials',
            'Material usage' => 'finna_usage_info',
        ]);
    }
}
