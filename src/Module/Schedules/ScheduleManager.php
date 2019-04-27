<?php

namespace App\Module\Schedules;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use App\Entity\LibraryInterface;
use App\Entity\Period;

class ScheduleManager
{
    const DATE_FORMAT = 'Y-m-d';

    public $db;

    public function __construct(Connection $database)
    {
        $this->db = $database;
    }

    /**
     * NOTE: Field 'opens' is used to deduce the date for a row. If 'closes' is NULL, then
     * the library is closed on that day. Live status is updated via a cron script.
     */
    public function loadSchedules(LibraryInterface $library, DateTimeImmutable $begin = null, DateTimeImmutable $end = null) : array
    {
        if (is_null($begin)) {
            $begin = new DateTimeImmutable('Monday this week');
        }

        if (is_null($end)) {
            $end = new DateTimeImmutable('+3 months');
        }

        $smt = $this->db->prepare('
            SELECT opens, closes, status, live_status, info, period
            FROM schedules
            WHERE library = ? AND date(opens) BETWEEN ? AND ?
            ORDER BY opens
        ');

        $smt->execute([$library->getId(), $begin->format(self::DATE_FORMAT), $end->format(self::DATE_FORMAT)]);
        $schedules = [];

        foreach ($smt->fetchAll(\PDO::FETCH_OBJ) as $row) {
            $date = substr($row->opens, 0, 10);
            $schedules[$date] = $row;

            $row->opens = new DateTimeImmutable($row->opens);
            $row->closes = $row->closes ? new DateTimeImmutable($row->closes) : null;
            $row->info = json_decode($row->info);
        }

        return $schedules;
    }

    public function updateSchedules(LibraryInterface $library, DateTimeImmutable $begin, DateTimeImmutable $end) : void
    {
        /*
         * NOTE: We're using a Doctrine Connection class here so commit() does not actually
         * flush and therefore nothing will/might(?) be written into the DB right there and then.
         */

        $periods = $library->getPeriods();
        $builder = new ScheduleBuilder();
        $schedules = $builder->build($periods, $begin, $end);

        $this->db->beginTransaction();

        $delete = $this->db->prepare('
            DELETE FROM schedules
            WHERE library = ? AND date(opens) BETWEEN ? AND ?
        ');

        $delete->execute([$library->getId(), $begin->format('Y-m-d'), $end->format('Y-m-d')]);

        $insert = $this->db->prepare('
            INSERT INTO schedules (period, library, department, info, opens, closes, status)
            VALUES (:period_id, :library_id, :department_id, :info, :opens, :closes, :status)
        ');

        foreach ($schedules as $date => $day_group) {
            foreach ($day_group as $department => $day) {
                $row = [
                    'library_id' => $library->getId(),
                    'period_id' => $day['period']->getId(),
                    'department_id' => $day['department'] ? $day['department']->getId() : null,
                    'info' => json_encode($day['info']),
                ];

                if ($day['closed']) {
                    $row += [
                        'opens' => (new DateTimeImmutable("{$date} 00:00:00"))->format(DateTime::RFC3339),
                        'closes' => null,
                        'status' => 0,
                    ];
                    $insert->execute($row);
                } else {
                    foreach ($day['times'] as $tuple) {
                        $row['opens'] = (new DateTimeImmutable("{$date} {$tuple['opens']}"))->format(DateTime::RFC3339);
                        $row['closes'] = (new DateTimeImmutable("{$date} {$tuple['closes']}"))->format(DateTime::RFC3339);
                        $row['status'] = $tuple['status'];

                        $insert->execute($row);
                        $row['info'] = null;
                    }
                }
            }
        }

        $this->db->commit();
    }
}
