<?php

namespace App\Module\Schedules\Exception;

class LegacyPeriodException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Cannot generate schedules as the interval contains a non-updated primary period');
    }
}
