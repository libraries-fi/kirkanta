<?php

namespace App\Module\Ptv\Util;

use DateTime;
use App\Entity\Period;
use App\Module\Schedules\ScheduleBuilder;

class OpeningTimes
{
    const MAX_TIME_IN_FUTURE = '+3 months';

    public static function acceptPeriod(Period $period) : bool
    {
        if ($period->getDepartment()) {
            return false;
        }

        if (($ends = $period->getValidUntil()) && $ends < (new DateTime)) {
            return false;
        }

        if ($period->getValidFrom() > new DateTime(self::MAX_TIME_IN_FUTURE)) {
            return false;
        }

        return true;
    }

    public static function periodDefinitions(Period $period) : array
    {
        if (count($period->getDays()) == 7) {
            // Use '12pm' as a quick hack to do away with the problem of generating UNIX timestamps
            // from timezone'd datetime values...
            $start_time = strtotime('Monday 12pm', $period->getValidFrom()->getTimestamp());
            $end_time = strtotime('Sunday 12pm', $start_time);
            // return [];
        } else {
            // Again, add 12 hours to avoid issues caused by combining unixtime with timezones...
            $start_time = strtotime($period->getValidFrom()->format('Y-m-d')) + 3600 * 12;
            $end_time = strtotime($period->getValidUntil()->format('Y-m-d')) + 3600 * 12;
        }

        $from = DateTime::createFromFormat('U', $start_time);
        $to = DateTime::createFromFormat('U', $end_time);

        return (new ScheduleBuilder)->buildRange($period, $from, $to);
    }
}
