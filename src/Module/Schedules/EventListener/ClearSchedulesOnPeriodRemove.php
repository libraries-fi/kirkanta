<?php

namespace App\Module\Schedules\EventListener;

use App\Entity\Period;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * The standard schedule event listener would be able to generate correct schedules after
 * a period is removed, but due to database constraints a period cannot be removed if it
 * has referencing schedule rows.
 *
 * This listener will first remove those offending rows and is therefore necessary.
 */
class ClearSchedulesOnPeriodRemove
{
    public function preRemove(Period $period, LifecycleEventArgs $args) : void
    {
        $db = $args->getEntityManager()->getConnection();
        $smt = $db->prepare('DELETE FROM schedules WHERE period = :period');
        $smt->execute(['period' => $period->getId()]);
    }
}
