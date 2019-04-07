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

    private $queue = [];

    public function __construct(ScheduleManager $schedule_manager, FlashBagInterface $flashes)
    {
        $this->manager = $schedule_manager;
        $this->flashes = $flashes;
    }

    public function postPersist(LifecycleEventArgs $event) : void
    {
        $entity = $event->getEntity();

        if ($entity instanceof Period && $entity->getParent() && $entity->getParent()->isPublished()) {
            $begin = $entity->getValidFrom();
            $end = $entity->getValidUntil();
            $fallback = new DateTime('+12 months');
            $end = $end ? min($end, $fallback) : $fallback;

            $this->queue[] = [$entity->getParent(), $begin, $end];
        }
    }

    public function preUpdate(PreUpdateEventArgs $event) : void
    {
        $entity = $event->getEntity();

        if ($entity instanceof Period && $entity->getParent() && $entity->getParent()->isPublished()) {
            $monday = new DateTime('Monday this week');
            if ($event->hasChangedField('valid_from')) {
                $begin = min($event->getOldValue('valid_from'), $event->getNewValue('valid_from'));
                $begin = max($begin, $monday);
            } else {
                $begin = max($entity->getValidFrom(), $monday);
            }

            if ($event->hasChangedField('valid_until')) {
                $end = max($event->getOldValue('valid_until'), $event->getNewValue('valid_until'));
            } else {
                $fallback = new DateTime('+12 months');
                $end = $entity->getValidUntil();
                $end = $end ? min($end, $fallback) : $fallback;
            }

            $this->queue[] = [$entity->getParent(), $begin, $end];
        }
    }

    public function preRemove(LifecycleEventArgs $event) : void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Period && $entity->getParent() && $entity->getParent()->isPublished()) {
            $begin = $entity->getValidFrom();
            $end = $entity->getValidUntil();
            $fallback = new DateTime('+12 months');
            $end = $end ? min($end, $fallback) : $fallback;

            $this->queue[] = [$entity->getParent(), $begin, $end];
        }
    }

    public function onKernelTerminate() : void
    {
        if (!$this->queue) {
            return;
        }

        foreach ($this->queue as $entry) {
            try {
                list($library, $begin, $end) = $entry;
                $this->manager->updateSchedules($library, $begin, $end);
            } catch (LegacyPeriodException $e) {
                $this->error();
            }
        }

        $this->queue = [];
    }

    private function error() : void
    {
        $this->flashes->add('danger', 'Schedules were not updated because the time range contained a legacy format period.');
    }
}
