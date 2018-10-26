<?php

namespace App\Util;

use App\I18n\StringMap;

class LibraryResources extends StringMap
{
    public function __construct()
    {
        parent::__construct([
            'contact_groups' => 'contact_info_group',
            'departments' => 'department',
            'email_addresses' => 'email_address',
            'links' => 'web_link',
            'periods' => 'period',
            'persons' => 'person',
            'phone_numbers' => 'phone',
            'pictures' => 'organisation_photo',
            'services' => 'service_instance'
        ]);
    }
}
