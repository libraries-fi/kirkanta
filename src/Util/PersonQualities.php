<?php

namespace App\Util;

use App\I18n\StringMap;

class PersonQualities extends StringMap
{
    public function __construct()
    {
        parent::__construct([
            'AV production' => 'av_production',
            'Book tips' => 'book_tips',
            'Children\'s library work' => 'children_library',
            'Communications' => 'communications',
            'Digital publishing' => 'e_publifications',
            'Education planning' => 'education_planning',
            'Education' => 'education',
            'Finances' => 'finances',
            'Games' => 'games',
            'Human resources' => 'human_resources',
            'Immigrant library work' => 'immigrant_library_work',
            'Indexing' => 'indexing',
            'Information retrieval' => 'information_retrieval',
            'Information technology' => 'information_technology',
            'International cooperation' => 'international_cooperation',
            'Library facility work' => 'library_facility_work',
            'Library systems' => 'library_systems',
            'Literature' => 'literature',
            'Media education' => 'media_education',
            'Mobile libraries' => 'mobile_libraries',
            'Music library work' => 'music_library_work',
            'Pedagogy' => 'pedagogy',
            'Remote services' => 'remote_services',
            'Seeking library work' => 'seeking_library_work',
            'Statistics' => 'statistics',
            'Web services' => 'web_services',
            'Youth library work' => 'youth_library_work',

            'Collections' => 'collections',
            'Customer service' => 'customer_service',
            'Materials purchases' => 'materials_purchases',
            'Materials selection' => 'materials_selection',
        ]);
    }
}
