<?php

namespace App\Module\Schedules\EventListener;

use App\Entity\Period;
use App\Entity\PeriodData;
use App\Module\Schedules\Exception\LegacyPeriodException;
use App\Module\Schedules\ScheduleManager;
use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class UpdateSchedules
{
    private $manager;
    private $flashes;

    public function __construct(ScheduleManager $schedule_manager, FlashBagInterface $flashes)
    {
        $this->manager = $schedule_manager;
        $this->flashes = $flashes;
    }

    public function postPersist(LifecycleEventArgs $event) : void
    {
        try {
            $entity = $event->getEntity();
            if ($entity instanceof Period && $entity->getParent() && $entity->getParent()->isPublished()) {
                $begin = $entity->getValidFrom();
                $end = $entity->getValidUntil();
                $fallback = new DateTime('+12 months');
                $end = $end ? min($end, $fallback) : $fallback;

                $this->manager->updateSchedules($entity->getParent(), $begin, $end);
            }
        } catch (LegacyPeriodException $e) {
            $this->error();
        }
    }

    public function preDelete(LifecycleEventArgs $event) : void
    {
        try {
            $entity = $event->getEntity();
            if ($entity instanceof Period && $entity->getParent() && $entity->getParent()->isPublished()) {
                $begin = $entity->getValidFrom();
                $end = $entity->getValidUntil();
                $fallback = new DateTime('+12 months');
                $end = $end ? min($end, $fallback) : $fallback;

                $this->manager->updateSchedules($entity->getParent(), $begin, $end);
            }
        } catch (LegacyPeriodException $e) {
            $this->error();
        }
    }

    public function preUpdate(PreUpdateEventArgs $event) : void
    {
        try {
            $entity = $event->getEntity();

            if ($entity instanceof Period && $entity->getParent() && $entity->getParent()->isPublished()) {
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

                $this->manager->updateSchedules($entity->getParent(), $begin, $end);
            }
        } catch (LegacyPeriodException $e) {
            $this->error();
        }
    }

    private function error() : void
    {
        $this->flashes->add('danger', 'Schedules were not updated because the time range contained a legacy format period.');
    }
}
