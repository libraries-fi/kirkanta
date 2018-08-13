<?php

namespace App\Module\Schedules;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use App\Entity\Library;
use App\Entity\Period;

class ScheduleManager
{
    const DATE_FORMAT = 'Y-m-d';

    private $db;

    public function __construct(Connection $database)
    {
        $this->db = $database;
    }

    /**
     * NOTE: Field 'opens' is used to deduce the date for a row. If 'closes' is NULL, then
     * the library is closed on that day. Field 'status' is the live status for the library.
     * (Updated via a cron script.)
     */
    public function loadSchedules(Library $library, DateTimeInterface $begin = NULL, DateTimeInterface $end = NULL) : array
    {
        if (is_null($begin)) {
            $begin = new DateTime('Monday this week');
        }

        if (is_null($end)) {
            $end = new DateTime('+3 months');
        }

        var_dump($begin, $end);

        $smt = $this->db->prepare('
            SELECT opens, closes, staff, status, info, period
            FROM schedules
            WHERE library = ? AND date(opens) BETWEEN ? AND ?
            ORDER BY opens
        ');

        $smt->execute([$library->getId(), $begin->format(self::DATE_FORMAT), $end->format(self::DATE_FORMAT)]);
        $schedules = [];

        foreach ($smt->fetchAll(\PDO::FETCH_OBJ) as $row) {
            $date = substr($row->opens, 0, 10);
            $schedules[$date] = $row;

            $row->opens = new DateTime($row->opens);
            $row->closes = $row->closes ? new DateTime($row->closes) : null;
            $row->info = json_decode($row->info);
        }

        return $schedules;
    }

    public function updateSchedules(Library $library, DateTimeInterface $begin, DateTimeInterface $end) : void
    {
        /*
         * NOTE: We're using a Doctrine Connection class here so commit() does not actually
         * flush and therefore nothing will/might(?) be written into the DB right there and then.
         */

        $periods = $library->getPeriods();
        $builder = new ScheduleBuilder;
        $schedules = $builder->build($periods, $begin, $end);

        $this->db->beginTransaction();

        $delete = $this->db->prepare('
            DELETE FROM schedules
            WHERE library = ? AND date(opens) BETWEEN ? AND ?
        ');

        $delete->execute([$library->getId(), $begin->format('Y-m-d'), $end->format('Y-m-d')]);

        $insert = $this->db->prepare('
            INSERT INTO schedules (period, library, department, info, opens, closes, staff)
            VALUES (:period_id, :library_id, :department_id, :info, :opens, :closes, :staff)
        ');

        foreach ($schedules as $day) {
            $row = [
                'library_id' => $library->getId(),
                'period_id' => $day['period']->getId(),
                'department_id' => $day['department'] ? $day['department']->getId() : $library->getId(),
                'info' => json_encode($day['info']),

                // This value is stored in the 'opens' field for every row.
                // 'date' => $day['date']->format('Y-m-d'),
            ];

            $date = $day['date']->format('Y-m-d');

            if ($day['closed']) {
                $row += [
                    // 'closed' => 't',
                    'opens' => (new DateTime("{$date} 00:00:00"))->format(DateTime::RFC3339),
                    'closes' => null,
                    'staff' => 'f',
                ];
                $insert->execute($row);
            } else {
                foreach ($day['times'] as $tuple) {
                    /*
                     * Using DateTime to make timestamps timezone-aware.
                     * NOTE: Postgresql converts times to UTC, hence the tz part will become +00.
                     * Don't be confused...
                     */
                    $row['opens'] = (new DateTime("{$date} {$tuple['opens']}"))->format(DateTime::RFC3339);
                    $row['closes'] = (new DateTime("{$date} {$tuple['closes']}"))->format(DateTime::RFC3339);
                    $row['staff'] = isset($tuple['staff']) ? ($tuple['staff'] ? 't' : 'f') : 't';

                    $insert->execute($row);
                }
            }

        }

        $this->db->commit();
    }
}
