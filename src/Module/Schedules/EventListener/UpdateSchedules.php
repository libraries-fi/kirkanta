<?php

namespace App\Module\Schedules\EventListener;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use App\Entity\Period;
use App\Entity\PeriodData;
use App\Module\Schedules\ScheduleManager;

class UpdateSchedules
{
    private $manager;

    public function __construct(ScheduleManager $schedule_manager)
    {
        $this->manager = $schedule_manager;
    }

    public function postPersist(LifecycleEventArgs $event) : void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Period && $entity->getLibrary() && $entity->getOrganisation()->isPublished()) {
            $begin = $entity->getValidFrom();
            $end = $entity->getValidUntil();
            $fallback = new DateTime('+12 months');
            $end = $end ? min($end, $fallback) : $fallback;

            $this->manager->updateSchedules($entity->getLibrary(), $begin, $end);
        }
    }

    public function preDelete(LifecycleEventArgs $event) : void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Period && $entity->getLibrary() && $entity->getOrganisation()->isPublished()) {
            $begin = $entity->getValidFrom();
            $end = $entity->getValidUntil();
            $fallback = new DateTime('+12 months');
            $end = $end ? min($end, $fallback) : $fallback;

            $this->manager->updateSchedules($entity->getLibrary(), $begin, $end);
        }
    }

    public function preUpdate(PreUpdateEventArgs $event) : void
    {
        $entity = $event->getEntity();

        if ($entity instanceof Period && $entity->getLibrary() && $entity->getOrganisation()->isPublished()) {
            if ($event->hasChangedField('valid_from')) {
                $begin = min($event->getOldValue('valid_from'), $event->getNewValue('valid_from'));
                $begin = max($begin, new DateTime);
            } else {
                $begin = max($entity->getValidFrom(), new DateTime);
            }

            if ($event->hasChangedField('valid_until')) {
                $end = max($event->getOldValue('valid_until'), $event->getNewValue('valid_until'));
            } else {
                $fallback = new DateTime('+12 months');
                $end = $entity->getValidUntil();
                $end = $end ? min($end, $fallback) : $fallback;
            }

            $this->manager->updateSchedules($entity->getLibrary(), $begin, $end);
        }
    }
}
