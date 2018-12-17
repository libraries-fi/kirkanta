<?php

namespace App\Doctrine\EventListener;

use App\Entity\Period;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ClearSchedulesOnPeriodRemove
{
    public function preRemove(Period $period, LifecycleEventArgs $args) : void
    {
        $db = $args->getEntityManager()->getConnection();
        $smt = $db->prepare('DELETE FROM schedules WHERE period = :period');
        $smt->execute(['period' => $period->getId()]);
    }
}
