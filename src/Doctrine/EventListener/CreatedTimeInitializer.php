<?php

namespace App\Doctrine\EventListener;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use App\Entity\Feature\CreatedAwareness;

class CreatedTimeInitializer implements EventSubscriber
{
    public function getSubscribedEvents() : array
    {
        return [Events::prePersist];
    }

    public function prePersist(LifecycleEventArgs $args) : void
    {
        $entity = $args->getEntity();

        if ($entity instanceof CreatedAwareness && !$entity->getCreated()) {
            $entity->setCreated(new DateTime());
        }
    }
}
