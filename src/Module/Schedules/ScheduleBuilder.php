<?php

namespace App\Module\Schedules;

use App\Entity\Period;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class ScheduleBuilder
{
    public function buildRange(Period $period, DateTimeImmutable $begin, DateTimeImmutable $end) : array
    {
        return $this->iteratePeriod($period, $begin, $end);
    }

    public function build(iterable $periods, DateTimeImmutable $begin = null, DateTimeImmutable $end = null) : array
    {
        if (is_null($begin)) {
            $begin = new DateTimeImmutable('Monday this week');
        }

        if (is_null($end)) {
            $end = new DateTimeImmutable('+3 months');
        }

        $end = clone $end;
        $end->setTime(23, 59, 59);

        $groups = $this->grouped($periods);
        $schedules = [];

        foreach ($groups as $department => $periods) {
            $periods = $this->filter($periods, $begin, $end);
            $days = [];

            foreach ($periods as $i => $period) {
                if ($period->isContinuous() && $period->isLegacyFormat()) {
                    throw new Exception\LegacyPeriodException;
                }

                if ($period->isContinuous()) {
                    if (isset($periods[$i+1]) and $periods[$i+1]->isContinuous()) {
                        $to = min($periods[$i+1]->getValidFrom(), $end);
                    } else {
                        $to = $end;
                    }
                } else {
                    $to = min($end, $period->getValidUntil());
                }
                $from = max($begin, $period->getValidFrom());
                $days = array_merge($days, $this->iteratePeriod($period, $from, $to));
            }

            foreach ($days as $date => $day) {
                if (!$department) {
                    // NOTE: Here '0' is a pseudo department ID.
                    $schedules[$date][0] = $day;
                } else {
                    if (!isset($schedules[$date])) {
                        // There is no base period (default section) for this day so we cannot continue.
                        continue;
                    }
                    unset($day['section'], $day['day'], $day['date'], $day['organisation']);
                    $schedules[$date][$department] = $day;
                }
            }
        }

        return $schedules;
    }

    private function grouped(iterable $periods) : array
    {
        $groups = [];

        foreach ($periods as $period) {
            $department = $period->getDepartment() ? $period->getDepartment()->getId() : 0;
            $groups[$department][] = $period;
        }

        foreach ($groups as $department => $periods) {
            $groups[$department] = $this->sort($periods);
        }

        ksort($groups);

        return $groups;
    }

    private function sort(array &$periods) : array
    {
        /*
         * Sort periods by start date and so that fixed-term periods come first.
         */
        usort($periods, function(Period $a, Period $b) : int {
            if ($a->isContinuous() ^ $b->isContinuous()) {
                return (int)$b->isContinuous() - (int)$a->isContinuous();
            }

            $diff = $b->getValidFrom()->diff($a->getValidFrom());

            if ($diff->days == 0) {
                return $this->getWeight($a) - $this->getWeight($b);
            } else {
                return $diff->days * ($diff->invert ? -1 : 1);
            }
        });

        return $periods;
    }

    private function filter(array $periods, DateTimeImmutable $begin, DateTimeImmutable $end) : array
    {
        $periods = array_filter($periods, function(Period $p) use($begin, $end) {
            if ($p->getSection() != 'default') {
                /*
                 * FIXME: Remove this check after removing support for legacy periods.
                 *
                 * Cannot index periods from legacy sections as they would mess up the generator.
                 */
                return false;
            }

            if (!$p->getDays()) {
                return false;
            }
            return $p->getValidFrom() <= $end && (!$p->getValidUntil() || $p->getValidUntil() >= $begin);
        });

        return $periods;
    }

    private function getWeight(Period $period) : int
    {
        if ($date = $period->getValidUntil()) {
            return $date->diff($period->getValidFrom())->days;
        } else {
            return 9999;
        }
    }

    /**
     * NOTE: This function requires that the passed dates $from and $to are also valid dates
     * in the context of $period, i.e. $period->getValidFrom() >= $from and $period->getValidUntil() <= $to.
     */
    private function iteratePeriod(Period $period, DateTimeImmutable $from, DateTimeImmutable $to) : array
    {
        $range = new DatePeriod($from->setTime(0, 0, 0), new DateInterval('P1D'), $to->setTime(23, 59, 59));
        $source = array_values($period->getDays());
        $schedules = [];

        $department = $period->getDepartment();
        $organisation = $period->getParent();

        if ($this->getWeight($period) < 7) {
            /**
             * With less than seven days, the first day in the period equals to the first date
             * when the period is in effect.
             */
            $index = $period->getValidFrom()->diff($from)->format('%a');
        } else {
            /**
             * When there are at least seven days in the period, the first day is always Monday
             * but the period's validity might start on another day.
             */
            $offset = $period->getValidFrom()->format('N') - 1;
            $delta = $period->getValidFrom()->diff($from)->format('%a');
            $index = ($delta + $offset) % count($period->getDays());
        }

        foreach ($range as $date) {
            $day = [
                'period' => $period,
                'organisation' => $organisation,
                'department' => $department,
                'date' => $date,
                'closed' => null,
                'info' => [],
                'times' => [],
            ];

            $day = array_replace($day, $source[$index % count($source)]);

            // Probably unneeded by now but some legacy data contained invalid, empty time entries.
            $day['times'] = array_filter($day['times'], function($time) {
                return !empty($time['opens']) && !empty($time['closes']);
            });

            // Reset keys after filtering.
            $day['times'] = array_values($day['times']);

            foreach ($day['times'] as $i => &$time) {
                if (!isset($time['staff'])) {
                    $time['staff'] = true;
                }
            }

            foreach ($day['times'] as $i => &$time) {
                $time['status'] = $time['staff'] ? 1 : 2;

                if ($i == 0) {
                    continue;
                }

                $prev = $day['times'][$i - 1];

                if ($prev['closes'] < $time['opens']) {
                    array_splice($day['times'], $i, 0, [[
                        'opens' => $prev['closes'],
                        'closes' => $time['opens'],
                        'status' => 0,

                        // The time entries should contain 'staff' because it is
                        // present on the form too, for data coherency.
                        'staff' => 0,
                    ]]);
                }
            }

            unset($time);

            if (empty($day['times'])) {
                $day['closed'] = true;
            } else {
                $day['closed'] = false;
            }

            $schedules[$date->format('Y-m-d')] = $day;
            $index++;
        }

        return $schedules;
    }
}
